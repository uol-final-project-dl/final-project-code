import sys
from colorthief import ColorThief
import json

def main():
    if len(sys.argv) != 2:
        print("Usage: caption.py <image-path>", file=sys.stderr)
        sys.exit(1)

    path = sys.argv[1]

    # Adapted from colorthief documentation
    color_thief = ColorThief(path)
    dominant_color = color_thief.get_color(quality=1)
    palette = color_thief.get_palette(color_count=6)

    result = {
        "dominant": dominant_color,
        "palette": palette
    }

    print(json.dumps(result))

if __name__ == "__main__":
    main()
