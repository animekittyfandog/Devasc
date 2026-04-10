import sys

# Windows: set before importing cv2 so HighGUI isn't initialized under DPI virtualization.
if sys.platform == "win32":
    try:
        import ctypes

        ctypes.windll.shcore.SetProcessDpiAwareness(2)  # per-monitor DPI aware
    except (AttributeError, OSError):
        try:
            ctypes.windll.user32.SetProcessDPIAware()
        except (AttributeError, OSError):
            pass

import cv2
import easyocr
import pandas as pd
from ultralytics import YOLO
import re

# 1. Setup
model = YOLO(r'runs\detect\ParkSense\v9e_initial_run2\weights\best.pt')
reader = easyocr.Reader(['en'], gpu=True) # Uses your RTX 3060
video_path = 'IMG_1094.MOV'
cap = cv2.VideoCapture(video_path)

data_log = []
frame_count = 0
_window_name = "ALPR Real-Time View (press q to quit)"
cv2.namedWindow(_window_name, cv2.WINDOW_AUTOSIZE)

while cap.isOpened():
    ret, frame = cap.read()
    if not ret:
        break

    frame_count += 1
    display_frame = frame.copy()
    # Change number in frame count to change the processing rate of the model (higher number = slower processing)
    if frame_count % 5 == 0:
        results = model(frame, verbose=False)

        for result in results:
            for box in result.boxes:
                cls_id = int(box.cls[0])
                class_name = model.names[cls_id]

                # content of data.yaml
                if class_name.lower() in ("car", "license plate"):
                    x1, y1, x2, y2 = map(int, box.xyxy[0])
                    conf = float(box.conf[0])

                    # Crop and OCR
                    plate_crop = frame[y1:y2, x1:x2]
                    # detail=0 returns just the text string
                    plate_crop = frame[y1:y2, x1:x2]
                    if plate_crop.size == 0:
                        continue

                    # Preprocess
                    gray = cv2.cvtColor(plate_crop, cv2.COLOR_BGR2GRAY)
                    gray = cv2.bilateralFilter(gray, 7, 75, 75)  # denoise while preserving edges
                    gray = cv2.resize(gray, None, fx=2.0, fy=2.0, interpolation=cv2.INTER_CUBIC)
                    thr = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                                cv2.THRESH_BINARY, 31, 2)

                    # detail=1 gives confidence per detection
                    ocr_out = reader.readtext(
                        thr,
                        detail=1,
                        allowlist='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
                    )

                    if ocr_out:
                        # choose highest confidence OCR candidate
                        best = max(ocr_out, key=lambda x: x[2])  # (bbox, text, conf)
                        plate_text = best[1].strip()
                        ocr_conf = float(best[2])

                        # filter weak OCR
                        if ocr_conf < 0.45:
                            plate_text = ""
                    else:
                        plate_text = ""
                        ocr_conf = 0.0
                    ocr_result = reader.readtext(plate_crop, detail=0)

                    plate_text = " ".join(ocr_result).strip()
                    normalized_plate = re.sub(r'[^A-Z0-9]', '', plate_text.upper())

                    # Draw detection for real-time preview
                    cv2.rectangle(display_frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
                    cv2.putText(
                        display_frame,
                        f"{plate_text if plate_text else 'no text'} ({conf:.2f})",
                        (x1, max(0, y1 - 10)),
                        cv2.FONT_HERSHEY_SIMPLEX,
                        0.6,
                        (0, 255, 0),
                        2
                    )

                    if plate_text and len(normalized_plate) > 3:
                        data_log.append({
                            "frame": frame_count,
                            "confidence": round(conf, 2),
                            "plate_text": plate_text,
                            "plate_text_normalized": normalized_plate
                        })
                        print(f"Frame {frame_count}: Detected Plate -> {plate_text}")

    cv2.imshow(_window_name, display_frame)
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()

# 2. Export to CSV
df = pd.DataFrame(data_log)
# Remove duplicates by normalized text, keep highest confidence per plate.
if not df.empty:
    df = df.sort_values(by="confidence", ascending=False)
    df = df.drop_duplicates(subset=["plate_text_normalized"], keep="first")
    df = df.sort_values(by="frame").drop(columns=["plate_text_normalized"])

df.to_csv('video_plates_results.csv', index=False)
print("Finished. Results saved to video_plates_results.csv")
