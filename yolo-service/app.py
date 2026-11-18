import os
import cv2
import numpy as np
import time
import threading
from flask import Flask, Response, jsonify, request
from flask_cors import CORS
from ultralytics import YOLO
from cap_from_youtube import cap_from_youtube

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})

# ==========================================
# 1. INISIALISASI 3 MODEL YOLOv8
# ==========================================
model_base = YOLO('yolov8n.pt')
model_tracking = YOLO('yolo_human_tracking.pt') if os.path.exists('yolo_human_tracking.pt') else None
model_height = YOLO('yolo_tinggi_badan.pt') if os.path.exists('yolo_tinggi_badan.pt') else None

active_models = ['yolov8n.pt']
if model_tracking:
    active_models.append('yolo_human_tracking.pt')
if model_height:
    active_models.append('yolo_tinggi_badan.pt')

print(f"[INFO] Arsitektur Multi-Model Aktif: {active_models}")

# ==========================================
# 2. KONFIGURASI KALIBRASI & SHARED STATE
# ==========================================
KNOWN_HEIGHT_CM = 170.0
REFERENCE_PIXEL_HEIGHT = 340.0
CM_PER_PIXEL = KNOWN_HEIGHT_CM / REFERENCE_PIXEL_HEIGHT

DEFAULT_VIDEO_SOURCE = "tester-cctv1.mp4"
YOUTUBE_RESOLUTION = 'best'

current_stats = {
    "detected_count": 0,
    "avg_height_cm": 0.0,
    "detected_heights": [],
    "status": "active",
    "models_used": active_models,
    "fusion_mode": "async_calibrated_ensemble"
}

# Shared memory untuk komunikasi antara Thread Utama (Real-Time) dan Thread Penunjang (Background)
shared_lock = threading.Lock()
latest_frame_for_aux = None
aux_tracking_boxes = []   # Hasil dari yolo_human_tracking.pt
aux_height_boxes = []     # Hasil dari yolo_tinggi_badan.pt
worker_running = False

# ==========================================
# 3. BACKGROUND WORKER UNTUK 2 MODEL KUSTOM
# ==========================================
def auxiliary_models_worker():
    """
    Menjalankan yolo_human_tracking.pt dan yolo_tinggi_badan.pt secara asinkron
    di latar belakang agar tidak pernah menghambat FPS video streaming utama.
    """
    global aux_tracking_boxes, aux_height_boxes, worker_running
    while worker_running:
        frame_copy = None
        with shared_lock:
            if latest_frame_for_aux is not None:
                frame_copy = latest_frame_for_aux.copy()

        if frame_copy is None:
            time.sleep(0.02)
            continue

        t_boxes = []
        h_boxes = []

        # Inferensi Model 2: Human Tracking
        if model_tracking is not None:
            try:
                res_t = model_tracking(frame_copy, stream=False, conf=0.55, verbose=False)
                for r in res_t:
                    for box in r.boxes:
                        x1, y1, x2, y2 = map(int, box.xyxy[0])
                        conf = float(box.conf[0])
                        if (y2 - y1) > (x2 - x1):  # Filter rasio postur berdiri
                            t_boxes.append([x1, y1, x2, y2, conf])
            except Exception:
                pass

        # Inferensi Model 3: Tinggi Badan
        if model_height is not None:
            try:
                res_h = model_height(frame_copy, stream=False, conf=0.55, verbose=False)
                for r in res_h:
                    for box in r.boxes:
                        x1, y1, x2, y2 = map(int, box.xyxy[0])
                        conf = float(box.conf[0])
                        if (y2 - y1) > (x2 - x1):
                            h_boxes.append([x1, y1, x2, y2, conf])
            except Exception:
                pass

        with shared_lock:
            aux_tracking_boxes = t_boxes
            aux_height_boxes = h_boxes

        # Beri jeda singkat agar CPU/GPU tetap adem dan ringan
        time.sleep(0.05)

# ==========================================
# 4. FUNGSI GEOMETRI, KALIBRASI & STREAM
# ==========================================
def compute_iou(boxA, boxB):
    xA = max(boxA[0], boxB[0])
    yA = max(boxA[1], boxB[1])
    xB = min(boxA[2], boxB[2])
    yB = min(boxA[3], boxB[3])

    inter_area = max(0, xB - xA) * max(0, yB - yA)
    if inter_area == 0:
        return 0.0

    boxA_area = max(1, (boxA[2] - boxA[0]) * (boxA[3] - boxA[1]))
    boxB_area = max(1, (boxB[2] - boxB[0]) * (boxB[3] - boxB[1]))
    return inter_area / float(boxA_area + boxB_area - inter_area)

def estimate_height(box_y1, box_y2, frame_height):
    pixel_height = abs(box_y2 - box_y1)
    if pixel_height == 0:
        return 0.0

    horizon_y = frame_height * 0.2
    normalized_y = (box_y2 - horizon_y) / (frame_height - horizon_y)
    normalized_y = max(0.1, min(1.0, normalized_y))

    estimated_height_cm = (pixel_height * CM_PER_PIXEL) / normalized_y
    return round(estimated_height_cm, 1)

def get_video_capture(source):
    if str(source) == '0' or str(source).lower() == 'webcam':
        print("[INFO] Membuka Integrated Camera (Device 0)...")
        return cv2.VideoCapture(0)
    elif isinstance(source, str) and ("youtube.com" in source or "youtu.be" in source):
        print(f"[INFO] Membuka Stream YouTube: {source}")
        try:
            return cap_from_youtube(source, resolution=YOUTUBE_RESOLUTION)
        except Exception as e:
            print(f"[ERROR] Gagal membuka stream YouTube: {e}")
            return None
    else:
        print(f"[INFO] Membuka File/CCTV Stream: {source}")
        return cv2.VideoCapture(source)

@app.route('/api/stats', methods=['GET', 'OPTIONS'])
def get_stats():
    return jsonify(current_stats), 200

def process_video(video_source):
    global current_stats, latest_frame_for_aux, worker_running

    # Nyalakan background worker untuk 2 model penunjang jika belum aktif
    if not worker_running and (model_tracking is not None or model_height is not None):
        worker_running = True
        t = threading.Thread(target=auxiliary_models_worker, daemon=True)
        t.start()

    cap = get_video_capture(video_source)

    while True:
        if cap is None or not cap.isOpened():
            print("[WARNING] Connection reset / Stream terputus. Mengulang koneksi dalam 2 detik...")
            time.sleep(2)
            cap = get_video_capture(video_source)
            continue

        success, frame = cap.read()

        if not success:
            if isinstance(video_source, str) and not ("youtube.com" in video_source or "youtu.be" in video_source):
                if str(video_source) not in ['0', 'webcam'] and not video_source.startswith("rtsp://"):
                    print("[INFO] Video selesai. Mengulang ke frame 0...")
                    cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
                    success, frame = cap.read()

            if not success:
                print("[WARNING] Frame tidak terbaca. Mereconnect stream...")
                cap.release()
                time.sleep(1)
                cap = get_video_capture(video_source)
                continue

        # Kirim frame terbaru ke background worker tanpa memblokir stream utama
        with shared_lock:
            latest_frame_for_aux = frame
            curr_track_boxes = list(aux_tracking_boxes)
            curr_height_boxes = list(aux_height_boxes)

        frame_h, frame_w, _ = frame.shape

        # 1. Deteksi Utama Real-Time menggunakan yolov8n.pt (mulus & cepat)
        results = model_base(frame, stream=True, classes=[0], verbose=False)

        final_detections = []
        for r in results:
            for box in r.boxes:
                x1, y1, x2, y2 = map(int, box.xyxy[0])
                confidence = float(box.conf[0])
                if confidence > 0.4:
                    final_detections.append([x1, y1, x2, y2, confidence, "base"])

        # 2. Kontribusi yolo_human_tracking.pt:
        # Menangkap orang yang terlewat di sudut sulit HANYA jika confidence sangat tinggi (>= 0.72) & tidak overlap
        for tb in curr_track_boxes:
            if tb[4] >= 0.72:
                is_overlap = any(compute_iou(tb[:4], det[:4]) >= 0.35 for det in final_detections)
                if not is_overlap:
                    final_detections.append([tb[0], tb[1], tb[2], tb[3], tb[4], "tracking"])

        heights_in_frame = []
        person_count = len(final_detections)

        for det in final_detections:
            x1, y1, x2, y2, conf, src = det

            # Hitung tinggi dasar dari kotak yang stabil
            base_height_cm = estimate_height(y1, y2, frame_h)
            final_height_cm = base_height_cm

            # 3. Kontribusi yolo_tinggi_badan.pt (Residual Calibration):
            # Jika model tinggi badan mendeteksi orang yang sama (IoU >= 0.65) & conf >= 0.65,
            # lakukan penyesuaian halus (dibatasi maks ±3.0 cm agar tetap akurat & tidak melompat)
            best_iou = 0.0
            matched_hbox = None
            for hb in curr_height_boxes:
                if hb[4] >= 0.65:
                    iou = compute_iou([x1, y1, x2, y2], hb[:4])
                    if iou > best_iou:
                        best_iou = iou
                        matched_hbox = hb[:4]

            if best_iou >= 0.65 and matched_hbox is not None:
                custom_height_cm = estimate_height(matched_hbox[1], matched_hbox[3], frame_h)
                delta = custom_height_cm - base_height_cm
                # Redam delta dengan bobot 25% dan batasi maksimal ±3.0 cm
                clamped_delta = max(-3.0, min(3.0, delta * 0.25))
                final_height_cm = round(base_height_cm + clamped_delta, 1)

            heights_in_frame.append(final_height_cm)

            label = f"Person: {final_height_cm} cm"
            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)

            (w, h), _ = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.5, 2)
            cv2.rectangle(frame, (x1, max(0, y1 - 22)), (x1 + w, y1), (0, 255, 0), -1)
            cv2.putText(frame, label, (x1, max(10, y1 - 5)), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 0, 0), 2)

        avg_height = round(float(np.mean(heights_in_frame)), 1) if heights_in_frame else 0.0

        current_stats["detected_count"] = person_count
        current_stats["avg_height_cm"] = avg_height
        current_stats["detected_heights"] = heights_in_frame

        summary_text = f"Pengunjung: {person_count} | Rata-Rata Tinggi: {avg_height} cm"
        cv2.putText(frame, summary_text, (15, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 255), 2)

        _, buffer = cv2.imencode('.jpg', frame)
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + buffer.tobytes() + b'\r\n')

    if cap:
        cap.release()

@app.route('/video_feed', methods=['GET', 'OPTIONS'])
def video_feed():
    if request.method == 'OPTIONS':
        response = Response()
        response.headers['Access-Control-Allow-Origin'] = '*'
        response.headers['Access-Control-Allow-Headers'] = '*'
        response.headers['Access-Control-Allow-Methods'] = 'GET, OPTIONS'
        return response

    source = request.args.get('source', DEFAULT_VIDEO_SOURCE)

    response = Response(
        process_video(source),
        mimetype='multipart/x-mixed-replace; boundary=frame'
    )

    response.headers['Cache-Control'] = 'no-cache, no-store, must-revalidate'
    response.headers['Pragma'] = 'no-cache'
    response.headers['Expires'] = '0'
    response.headers['Access-Control-Allow-Origin'] = '*'
    response.headers['Access-Control-Allow-Headers'] = '*'
    return response

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)