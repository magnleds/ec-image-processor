from PIL import Image
import os, oxipng

INPUT = '/Users/deffipy/Desktop/test'
OUTPUT = '/Users/deffipy/Desktop/test/output'
SIZE = (800, 800)

os.makedirs(OUTPUT, exist_ok=True)

fnames = [f for f in os.listdir(INPUT)
          if not f.startswith('.') and f != 'output'
          and f.lower().rsplit('.', 1)[-1] in ('jpg', 'jpeg', 'png')]

total = len(fnames)
for i, fname in enumerate(fnames, 1):
    ext = fname.lower().rsplit('.', 1)[-1]
    src = os.path.join(INPUT, fname)
    img = Image.open(src)

    # PNG 补白底
    if img.mode in ('RGBA', 'LA') or (img.mode == 'P' and 'transparency' in img.info):
        bg = Image.new('RGB', img.size, (255, 255, 255))
        if img.mode == 'P':
            img = img.convert('RGBA')
        bg.paste(img, mask=img.split()[-1] if img.mode == 'RGBA' else None)
        img = bg
    else:
        img = img.convert('RGB')

    # 等比缩放，居中放在白底画布
    img.thumbnail(SIZE, Image.LANCZOS)
    canvas = Image.new('RGB', SIZE, (255, 255, 255))
    offset = ((SIZE[0] - img.width) // 2, (SIZE[1] - img.height) // 2)
    canvas.paste(img, offset)

    if ext == 'png':
        out_name = os.path.splitext(fname)[0] + '.png'
        out_path = os.path.join(OUTPUT, out_name)
        canvas.save(out_path, 'PNG')
        oxipng.optimize(out_path, level=4)
    else:
        out_name = os.path.splitext(fname)[0] + '.jpg'
        out_path = os.path.join(OUTPUT, out_name)
        canvas.save(out_path, 'JPEG', quality=95, optimize=True)

    size_kb = os.path.getsize(out_path) // 1024
    print(f'[{i}/{total}] ✓ {fname} → {out_name} ({size_kb}KB)')

print('完成！')
