import cv2
import numpy as np
import time
from flask import Flask, Response, jsonify, request
from flask_cors import CORS
from ultralytics import YOLO
from cap_from_youtube import cap_from_youtube

app = Flask(__name__)
CORS(app)

model = YOLO('yolov8n.pt')

KNOWN_HEIGHT_CM = 170.0
REFERENCE_PIXEL_HEIGHT = 340.0
CM_PER_PIXEL = KNOWN_HEIGHT_CM / REFERENCE_PIXEL_HEIGHT

DEFAULT_VIDEO_SOURCE = "https://www.youtube.com/watch?v=KMJS66jBtVQ&pp=ygUccmVrYW1hbiBjY3R2IHBlbmd1bmp1bmcgdG9rb9IHCQk_DAGHKiGM7w%3D%3D"
YOUTUBE_RESOLUTION = '360p'

current_stats = {
    "detected_count": 0,
    "avg_height_cm": 0.0,
    "detected_heights": [],
    "status": "active"
}

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
            # Gunakan cap_from_youtube dengan penanganan exception
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
    global current_stats
    cap = get_video_capture(video_source)

    while True:
        # Jika stream gagal atau terputus
        if cap is None or not cap.isOpened():
            print("[WARNING] Connection reset / Stream terputus. Mengulang koneksi dalam 2 detik...")
            time.sleep(2)
            cap = get_video_capture(video_source)
            continue

        success, frame = cap.read()
        if not success:
            print("[WARNING] Frame tidak terbaca. Mereconnect stream...")
            cap.release()
            time.sleep(1)
            cap = get_video_capture(video_source)
            
            # Khusus file lokal MP4
            if isinstance(video_source, str) and not ("youtube.com" in video_source or "youtu.be" in video_source):
                if str(video_source) not in ['0', 'webcam'] and not video_source.startswith("rtsp://"):
                    if cap and cap.isOpened():
                        cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
            continue

        frame_h, frame_w, _ = frame.shape

        # Deteksi YOLOv8
        results = model(frame, stream=True, classes=[0])

        heights_in_frame = []
        person_count = 0

        for r in results:
            boxes = r.boxes
            person_count = len(boxes)

            for box in boxes:
                x1, y1, x2, y2 = map(int, box.xyxy[0])
                confidence = float(box.conf[0])

                if confidence > 0.4:
                    height_cm = estimate_height(y1, y2, frame_h)
                    heights_in_frame.append(height_cm)

                    label = f"Person: {height_cm} cm"
                    cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)

                    (w, h), _ = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.5, 2)
                    cv2.rectangle(frame, (x1, y1 - 22), (x1 + w, y1), (0, 255, 0), -1)
                    cv2.putText(frame, label, (x1, y1 - 5), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 0, 0), 2)

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

@app.route('/video_feed')
def video_feed():
    source = request.args.get('source', DEFAULT_VIDEO_SOURCE)
    return Response(process_video(source), mimetype='multipart/x-mixed-replace; boundary=frame')

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)