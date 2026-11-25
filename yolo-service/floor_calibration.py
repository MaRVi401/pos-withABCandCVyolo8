"""
Kalibrasi lantai per-CCTV.

Pengguna menandai 4 sudut lantai (persegi panjang) di gambar CCTV dan mengisi
ukuran aslinya (cm). Dari situ dihitung:
  1. Homografi lantai  -> posisi kaki orang di lantai (cm)
  2. Pose kamera (fokal & tinggi kamera) dari homografi tersebut
  3. Tinggi badan  -> titik kepala & kaki di gambar dipetakan ke sumbu vertikal (Z)

Titik lantai: TL=(0,0)  TR=(W,0)  BR=(W,L)  BL=(0,L)
W = lebar (TL->TR), L = panjang/kedalaman (TR->BR), satuan cm.
"""
import json
import os
import threading

import cv2
import numpy as np

CALIB_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), "calibrations.json")
_lock = threading.Lock()
_cache = {}          # source -> dict kalibrasi (siap pakai, berisi matriks numpy)
_loaded = False


# ---------------------------------------------------------------- matematika
def _estimate_focal(H, cx, cy, fallback):
    """Taksir panjang fokal (piksel) dari homografi lantai (kendala ortogonalitas)."""
    def shift(h):
        return np.array([h[0] - cx * h[2], h[1] - cy * h[2], h[2]])
    h1, h2 = shift(H[:, 0]), shift(H[:, 1])
    cands = []
    d1 = h1[2] * h2[2]
    if abs(d1) > 1e-9:
        cands.append((abs(d1), -(h1[0] * h2[0] + h1[1] * h2[1]) / d1))
    d2 = h1[2] ** 2 - h2[2] ** 2
    if abs(d2) > 1e-9:
        cands.append((abs(d2), -(h1[0] ** 2 + h1[1] ** 2 - h2[0] ** 2 - h2[1] ** 2) / d2))
    cands = [(w, f2) for w, f2 in cands if f2 > 0]
    if not cands:
        return fallback, False
    f2 = max(cands)[1]            # pakai kendala dengan penyebut terbesar (paling stabil)
    f = float(np.sqrt(f2))
    if not (0.3 * fallback <= f <= 6 * fallback):
        return fallback, False
    return f, True


def build_calibration(points, width_cm, length_cm, frame_w, frame_h):
    """points: 4 titik [x,y] piksel urut TL, TR, BR, BL. Return dict kalibrasi atau raise ValueError."""
    pts = np.array(points, dtype=np.float64)
    if pts.shape != (4, 2):
        raise ValueError("Butuh tepat 4 titik sudut lantai.")
    if width_cm <= 0 or length_cm <= 0:
        raise ValueError("Lebar dan panjang lantai harus lebih dari 0.")
    # Sudut lantai boleh berada sedikit di luar gambar (lantai yang melewati tepi kamera), maksimal 50% dari ukuran gambar
    mx, my = 0.5 * frame_w, 0.5 * frame_h
    if (np.any(pts[:, 0] < -mx) or np.any(pts[:, 0] > frame_w + mx)
            or np.any(pts[:, 1] < -my) or np.any(pts[:, 1] > frame_h + my)):
        raise ValueError("Ada titik terlalu jauh di luar area gambar.")

    # titik harus membentuk segi empat konveks (tidak menyilang)
    cross = []
    for i in range(4):
        a, b, c = pts[i], pts[(i + 1) % 4], pts[(i + 2) % 4]
        cross.append((b[0] - a[0]) * (c[1] - b[1]) - (b[1] - a[1]) * (c[0] - b[0]))
    if not (all(c > 0 for c in cross) or all(c < 0 for c in cross)):
        raise ValueError("Urutan titik harus TL, TR, BR, BL dan tidak menyilang.")

    floor = np.array([[0, 0], [width_cm, 0], [width_cm, length_cm], [0, length_cm]], dtype=np.float64)
    H, _ = cv2.findHomography(floor, pts)          # lantai(cm) -> piksel
    if H is None:
        raise ValueError("Homografi gagal dihitung, periksa titik sudut.")
    H = H / H[2, 2]
    H_inv = np.linalg.inv(H)

    cx, cy = frame_w / 2.0, frame_h / 2.0
    fallback_f = float(max(frame_w, frame_h))
    f, f_ok = _estimate_focal(H, cx, cy, fallback_f)
    K = np.array([[f, 0, cx], [0, f, cy], [0, 0, 1]])

    G = np.linalg.inv(K) @ H
    lam = 1.0 / np.linalg.norm(G[:, 0])
    if G[2, 2] * lam < 0:
        lam = -lam
    r1, r2, t = G[:, 0] * lam, G[:, 1] * lam, G[:, 2] * lam
    r3 = np.cross(r1, r2)
    R = np.column_stack([r1, r2, r3])
    C = -R.T @ t                                    # posisi kamera di koordinat lantai
    if C[2] < 0:                                    # buat sumbu Z menunjuk ke atas
        r3 = -r3
        R = np.column_stack([r1, r2, r3])
        C = -R.T @ t
    cam_height = float(C[2])
    if cam_height <= 0:
        raise ValueError("Pose kamera tidak valid, ulangi penandaan sudut lantai.")

    return {
        "H": H, "H_inv": H_inv, "K": K, "R": R, "t": t,
        "p3": K @ r3, "focal_px": float(f), "focal_estimated": bool(f_ok),
        "camera_height_cm": cam_height,
        "frame_w": int(frame_w), "frame_h": int(frame_h),
        "points": pts.tolist(), "width_cm": float(width_cm), "length_cm": float(length_cm),
    }


def height_from_box(calib, x1, y1, x2, y2):
    """Tinggi (cm) dari bounding box orang. None jika di luar jangkauan model (mis. di atas horizon)."""
    fx = (x1 + x2) / 2.0
    feet = np.array([fx, float(y2), 1.0])
    head = np.array([fx, float(y1), 1.0])

    XY = calib["H_inv"] @ feet
    if abs(XY[2]) < 1e-9:
        return None
    X, Y = XY[0] / XY[2], XY[1] / XY[2]

    P0 = calib["K"] @ (calib["R"][:, 0] * X + calib["R"][:, 1] * Y + calib["t"])
    a = np.cross(head, P0)
    b = np.cross(head, calib["p3"])
    denom = float(b @ b)
    if denom < 1e-12:
        return None
    Z = -float(a @ b) / denom
    if not (30.0 <= Z <= 260.0):                    # di luar rentang manusia -> abaikan
        return None
    return round(Z, 1)


def foot_to_floor(calib, x1, y1, x2, y2):
    """Posisi kaki di lantai (cm) relatif ke sudut TL. Berguna untuk debug/peta lantai."""
    XY = calib["H_inv"] @ np.array([(x1 + x2) / 2.0, float(y2), 1.0])
    return float(XY[0] / XY[2]), float(XY[1] / XY[2])


# ---------------------------------------------------------------- penyimpanan
def _to_public(c):
    return {k: c[k] for k in ("points", "width_cm", "length_cm", "frame_w", "frame_h")}


def _load_all():
    global _loaded
    if _loaded:
        return
    _loaded = True
    if not os.path.exists(CALIB_FILE):
        return
    try:
        with open(CALIB_FILE, "r", encoding="utf-8") as fh:
            raw = json.load(fh)
        for src, d in raw.items():
            try:
                _cache[src] = build_calibration(d["points"], d["width_cm"], d["length_cm"], d["frame_w"], d["frame_h"])
            except Exception as e:
                print(f"[WARN] Kalibrasi '{src}' dilewati: {e}")
    except Exception as e:
        print(f"[WARN] Gagal membaca {CALIB_FILE}: {e}")


def _persist():
    data = {src: _to_public(c) for src, c in _cache.items()}
    tmp = CALIB_FILE + ".tmp"
    with open(tmp, "w", encoding="utf-8") as fh:
        json.dump(data, fh, indent=2)
    os.replace(tmp, CALIB_FILE)


def get(source):
    with _lock:
        _load_all()
        return _cache.get(str(source))


def save(source, points, width_cm, length_cm, frame_w, frame_h):
    calib = build_calibration(points, width_cm, length_cm, frame_w, frame_h)
    with _lock:
        _load_all()
        _cache[str(source)] = calib
        _persist()
    return calib


def delete(source):
    with _lock:
        _load_all()
        existed = _cache.pop(str(source), None) is not None
        if existed:
            _persist()
        return existed


def summary(calib):
    if calib is None:
        return None
    d = _to_public(calib)
    d["focal_px"] = round(calib["focal_px"], 1)
    d["focal_estimated"] = calib["focal_estimated"]
    d["camera_height_cm"] = round(calib["camera_height_cm"], 1)
    return d
