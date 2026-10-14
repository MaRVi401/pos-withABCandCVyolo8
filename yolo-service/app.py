from flask import Flask, Response, jsonify
from flask_cors import CORS
import cv2
import numpy as np
from ultralytics import YOLO

app = Flask(__name__)
# Enable CORS untuk semua route agar frontend (Laravel/React/Flutter) bisa akses API
CORS(app)

# Load model YOLOv8 (Class 0 = Person)
model = YOLO('yolov8n.pt')

# --- PARAMETER KALIBRASI TINGGI BADAN ---
# Ganti nilai ini sesuai hasil kalibrasi area CCTV
# Contoh: Tinggi acuan 170 cm terdeteksi setinggi 340 piksel pada jarak standar
KNOWN_HEIGHT_CM = 170.0
REFERENCE_PIXEL_HEIGHT = 340.0
CM_PER_PIXEL = KNOWN_HEIGHT_CM / REFERENCE_PIXEL_HEIGHT

# Data statistik global real-time
current_stats = {
    "detected_count": 0,
    "avg_height_cm": 0.0,
    "detected_heights": [],
    "status": "active"
}

def estimate_height(box_y1, box_y2, frame_height):
    """
    Mengestimasi tinggi badan berdasarkan bounding box y1 dan y2
    dengan koreksi perspektif posisi kaki (y2).
    """
    pixel_height = abs(box_y2 - box_y1)
    if pixel_height == 0:
        return 0.0

    # Koreksi perspektif bidang lantai
    horizon_y = frame_height * 0.2  # Asumsi horizon pada 20% atas frame
    normalized_y = (box_y2 - horizon_y) / (frame_height - horizon_y)
    normalized_y = max(0.1, min(1.0, normalized_y))

    # Kalkulasi tinggi dalam cm
    estimated_height_cm = (pixel_height * CM_PER_PIXEL) / normalized_y
    return round(estimated_height_cm, 1)

# --- ENDPOINT STATISTIK FOR FRONTEND ---
@app.route('/api/stats', methods=['GET', 'OPTIONS'])
def get_stats():
    return jsonify(current_stats), 200

# Function video processing
def process_video():
    global current_stats
    cap = cv2.VideoCapture("tester-cctv1.mp4")

    while cap.isOpened():
        success, frame = cap.read()
        if not success:
            cap.set(cv2.CAP_PROP_POS_FRAMES, 0)  # Loop video jika selesai
            continue

        frame_h, frame_w, _ = frame.shape

        # Deteksi manusia saja (class 0)
        results = model(frame, stream=True, classes=[0])

        heights_in_frame = []
        person_count = 0

        for r in results:
            boxes = r.boxes
            person_count = len(boxes)

            for box in boxes:
                # Koordinat Bounding Box
                x1, y1, x2, y2 = map(int, box.xyxy[0])
                confidence = float(box.conf[0])

                if confidence > 0.4:
                    # Hitung estimasi tinggi badan
                    height_cm = estimate_height(y1, y2, frame_h)
                    heights_in_frame.append(height_cm)

                    # Visualisasi Bounding Box
                    label = f"Person: {height_cm} cm"
                    cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)

                    # Text Overlay untuk Tinggi Badan
                    (w, h), _ = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.5, 2)
                    cv2.rectangle(frame, (x1, y1 - 22), (x1 + w, y1), (0, 255, 0), -1)
                    cv2.putText(frame, label, (x1, y1 - 5), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 0, 0), 2)

        # Hitung Rata-Rata Tinggi Badan Frame Saat Ini
        avg_height = round(float(np.mean(heights_in_frame)), 1) if heights_in_frame else 0.0

        # Update data statistik global real-time
        current_stats["detected_count"] = person_count
        current_stats["avg_height_cm"] = avg_height
        current_stats["detected_heights"] = heights_in_frame

        # Render Info Ringkasan di pojok kiri atas video
        summary_text = f"Pengunjung: {person_count} | Rata-Rata Tinggi: {avg_height} cm"
        cv2.putText(frame, summary_text, (15, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 255), 2)

        # Stream Frame MJPEG
        _, buffer = cv2.imencode('.jpg', frame)
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + buffer.tobytes() + b'\r\n')

@app.route('/video_feed')
def video_feed():
    return Response(process_video(), mimetype='multipart/x-mixed-replace; boundary=frame')

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)