import sys
from PIL import Image
from transformers import Blip2Processor, Blip2ForConditionalGeneration

def main():
    if len(sys.argv) != 2:
        print("Usage: caption.py <image-path>", file=sys.stderr)
        sys.exit(1)

    path = sys.argv[1]
    processor = Blip2Processor.from_pretrained("Salesforce/blip2-flan-t5-xxl")
    model = Blip2ForConditionalGeneration.from_pretrained("Salesforce/blip2-flan-t5-xxl")

    img = Image.open(path).convert("RGB")
    inputs = processor(images=img, return_tensors="pt")
    out = model.generate(**inputs, max_new_tokens=128)

    caption = processor.decode(out[0], skip_special_tokens=True)
    print(caption)

if __name__ == "__main__":
    main()
