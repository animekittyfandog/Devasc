import cv2
import easyocr
import pandas as pd
from ultralytics import YOLO

# 1. Setup
model = YOLO('best.pt')
reader = easyocr.Reader(['en'], gpu=True) # Uses your RTX 3060
video_path = 'traffic_video.mp4'
cap = cv2.VideoCapture(video_path)

data_log = []
frame_count = 0

while cap.isOpened():
    ret, frame = cap.read()
    if not ret:
        break

    frame_count += 1
    # Process every 5th frame to save on GPU usage
    if frame_count % 5 != 0:
        continue

    results = model(frame, verbose=False)

    for result in results:
        for box in result.boxes:
            cls_id = int(box.cls[0])
            class_name = model.names[cls_id]

            # Only target the "plates" class
            if class_name.lower() == 'plates':
                x1, y1, x2, y2 = map(int, box.xyxy[0])
                conf = float(box.conf[0])

                # Crop and OCR
                plate_crop = frame[y1:y2, x1:x2]
                # detail=0 returns just the text string
                ocr_result = reader.readtext(plate_crop, detail=0)
                
                plate_text = " ".join(ocr_result).strip()

                if plate_text:
                    data_log.append({
                        "frame": frame_count,
                        "confidence": round(conf, 2),
                        "plate_text": plate_text
                    })
                    print(f"Frame {frame_count}: Detected Plate -> {plate_text}")

cap.release()

# 2. Export to CSV
df = pd.DataFrame(data_log)
# Clean up: Remove very short strings that might be noise (e.g., bolts/screws)
df = df[df['plate_text'].str.len() > 3] 

df.to_csv('video_plates_results.csv', index=False)
print("Finished. Results saved to video_plates_results.csv")
