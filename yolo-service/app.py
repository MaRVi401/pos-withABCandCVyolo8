import cv2
from flask import Flask, Response, jsonify
from flask_cors import CORS
from ultralytics import YOLO

app = Flask(__name__)
CORS(app)  # Memizinkan permintaan Cross-Origin dari Laravel

# 1. Load Model YOLOv8
model = YOLO('yolov8n.pt')

# 2. Path Video / CCTV Stream (Sesuaikan dengan file video atau RTSP CCTV kamu)
# Untuk webcam laptop, ganti dengan: VIDEO_SOURCE = 0
VIDEO_SOURCE = "tester-cctv1.mp4" 

# Koordinat Garis Batas Counting
COUNT_LINE_Y = 300

# Global Stats untuk API Response
stats_data = {
    "total_detected": 0
}

def generate_frames():
    cap = cv2.VideoCapture(VIDEO_SOURCE)

    while True:
        success, frame = cap.read()
        if not success:
            # Jika video file habis, loop kembali ke awal
            cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
            continue

        # Jalankan YOLOv8 Tracking khusus kelas 'person' (Class ID 0)
        results = model.track(frame, persist=True, classes=[0], verbose=False)

        # Gambar Garis Batas Counting
        cv2.line(frame, (0, COUNT_LINE_Y), (frame.shape[1], COUNT_LINE_Y), (0, 255, 0), 2)

        current_count = 0
        if results[0].boxes is not None and results[0].boxes.id is not None:
            boxes = results[0].boxes.xyxy.cpu().numpy()
            track_ids = results[0].boxes.id.cpu().numpy()
            current_count = len(track_ids)

            for box, track_id in zip(boxes, track_ids):
                x1, y1, x2, y2 = map(int, box)

                # Bounding Box & ID
                cv2.rectangle(frame, (x1, y1), (x2, y2), (255, 0, 0), 2)
                cv2.putText(frame, f"ID: {int(track_id)}", (x1, y1 - 10),
                            cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 255, 255), 2)

        # Update data global statistik
        stats_data["total_detected"] = current_count
        print(f"[DEBUG] Jumlah Orang Terdeteksi: {current_count}")

        # Encode Frame ke JPEG Format
        ret, buffer = cv2.imencode('.jpg', frame)
        frame_bytes = buffer.tobytes()

        # Generator Stream MJPEG
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')

    cap.release()

@app.route('/video_feed')
def video_feed():
    """Endpoint untuk stream video MJPEG ke Tag <img> di Blade Laravel"""
    return Response(generate_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/api/stats', methods=['GET'])
def get_stats():
    """Endpoint API JSON untuk data statistik realtime (diambil via AJAX di Laravel)"""
    return jsonify(stats_data)

if __name__ == '__main__':
    # Jalankan service Flask di port 5000
    app.run(host='0.0.0.0', port=5000, debug=True)