import sys
import requests
from PIL import Image
from transformers import Blip2Processor, Blip2ForConditionalGeneration

def main():
    if len(sys.argv) != 2:
        print("Usage: caption.py <image-path>", file=sys.stderr)
        sys.exit(1)

    path = sys.argv[1]
    processor = Blip2Processor.from_pretrained("Salesforce/blip2-opt-2.7b")
    model = Blip2ForConditionalGeneration.from_pretrained("Salesforce/blip2-opt-2.7b")

    raw_image = Image.open(path).convert("RGB")
    question = 'Convert this image to HTML'
    inputs = processor(raw_image,question, return_tensors="pt")
    out = model.generate(**inputs)

    caption = processor.decode(out[0], skip_special_tokens=True)
    print(caption)

if __name__ == "__main__":
    main()
