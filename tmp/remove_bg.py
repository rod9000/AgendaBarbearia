from PIL import Image
import sys

def remove_white_background(input_path, output_path, threshold=240):
    img = Image.open(input_path).convert("RGBA")
    data = img.getdata()

    new_data = []
    for item in data:
        if item[0] > threshold and item[1] > threshold and item[2] > threshold:
            new_data.append((255, 255, 255, 0))
        else:
            new_data.append(item)

    img.putdata(new_data)
    img.save(output_path, "PNG")
    print(f"Salvo: {output_path}")

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Uso: python remove_bg.py <input> <output> [threshold]")
        sys.exit(1)
    threshold = int(sys.argv[3]) if len(sys.argv) > 3 else 240
    remove_white_background(sys.argv[1], sys.argv[2], threshold)
