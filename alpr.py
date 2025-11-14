import torch
import cv2
import easyocr
import os
import sys
import json

# --- Configuration ---
# Path to your YOLO weights, relative to this script's location
YOLO_WEIGHTS_PATH = 'weights/best.pt' 
# The class ID for 'license-plate' in your dataset's .yaml file
LICENSE_PLATE_CLASS_ID = 1 
# ---

def run_alpr(image_path):
    """Runs YOLO detection and EasyOCR recognition."""
    
    # 1. Load YOLO Model
    try:
        # Load the custom model. Adjust 'path/to/yolov9/repo' if necessary 
        # based on how your environment is configured for the Ultralytics framework.
        model = torch.hub.load('C:/xampp/htdocs/Devasc', 'custom', path=YOLO_WEIGHTS_PATH, source='local') 
        model.conf = 0.5    # Confidence threshold
        model.classes = [LICENSE_PLATE_CLASS_ID] 
    except Exception as e:
        return {"error": f"Failed to load YOLO model: {e}"}

    # 2. Load EasyOCR Reader 
    # Uses GPU (RTX 3060) if available: torch.cuda.is_available()
    reader = easyocr.Reader(['en'], gpu=torch.cuda.is_available())

    # 3. Load Image and Inference
    img = cv2.imread(image_path)
    if img is None:
        return {"error": f"Failed to load image: {image_path}"}
    
    results = model(img)
    detections = results.pandas().xyxy[0] 
    license_plates = detections[detections['name'] == 'license-plate'] 

    final_results = []

    for index, row in license_plates.iterrows():
        # Get coordinates and crop the plate
        x_min, y_min, x_max, y_max = int(row['xmin']), int(row['ymin']), int(row['xmax']), int(row['ymax'])
        plate_crop = img[y_min:y_max, x_min:x_max]
        
        # Run OCR Recognition: allowlist restricts output to alphanumeric characters
        ocr_results = reader.readtext(plate_crop, allowlist='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', detail=0) 

        if ocr_results:
            plate_text = "".join(ocr_results).replace(" ", "") 
            
            final_results.append({
                "plate_text": plate_text,
                "confidence": float(row['confidence']),
                "bbox": [x_min, y_min, x_max, y_max]
            })
            
    return final_results

# Executes when the script is called from the command line (by the web server)
if __name__ == '__main__':
    if len(sys.argv) < 2:
        # Handle error if the image path is not provided
        print(json.dumps({"error": "No image path provided."}))
        sys.exit(1)
        
    image_to_process = sys.argv[1]
    results = run_alpr(image_to_process)
    
    # Output the results as a JSON string
    print(json.dumps(results))