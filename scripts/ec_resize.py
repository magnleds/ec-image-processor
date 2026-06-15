from PIL import Image
import os, sys, oxipng

def main():
    if len(sys.argv) < 2:
        print('用法: python3 ec_resize.py <输入目录> [输出目录]')
        print('示例: python3 ec_resize.py ~/Desktop/产品图')
        sys.exit(1)

    INPUT  = os.path.expanduser(sys.argv[1])
    OUTPUT = os.path.expanduser(sys.argv[2]) if len(sys.argv) > 2 else os.path.join(INPUT, 'output')
    SIZE   = (800, 800)

    if not os.path.isdir(INPUT):
        print(f'错误：目录不存在 → {INPUT}')
        sys.exit(1)

    os.makedirs(OUTPUT, exist_ok=True)

    fnames = [f for f in os.listdir(INPUT)
              if not f.startswith('.')
              and os.path.normpath(os.path.join(INPUT, f)) != os.path.normpath(OUTPUT)
              and f.lower().rsplit('.', 1)[-1] in ('jpg', 'jpeg', 'png')]

    total = len(fnames)
    if not total:
        print('没有找到图片（jpg/jpeg/png）')
        sys.exit(0)

    print(f'找到 {total} 张图片，输出到 {OUTPUT}\n')
    saved_total = 0

    for i, fname in enumerate(fnames, 1):
        ext     = fname.lower().rsplit('.', 1)[-1]
        src     = os.path.join(INPUT, fname)
        orig_kb = os.path.getsize(src) // 1024
        img     = Image.open(src)

        # 补白底
        if img.mode in ('RGBA', 'LA') or (img.mode == 'P' and 'transparency' in img.info):
            bg = Image.new('RGB', img.size, (255, 255, 255))
            if img.mode == 'P':
                img = img.convert('RGBA')
            bg.paste(img, mask=img.split()[-1] if img.mode == 'RGBA' else None)
            img = bg
        else:
            img = img.convert('RGB')

        # 等比缩放居中
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

        out_kb  = os.path.getsize(out_path) // 1024
        saved   = orig_kb - out_kb
        saved_total += saved
        ratio   = int((1 - out_kb / orig_kb) * 100) if orig_kb else 0
        print(f'[{i}/{total}] {fname} → {out_name}  {orig_kb}KB → {out_kb}KB  (-{ratio}%)')

    print(f'\n完成！共节省 {saved_total}KB')

if __name__ == '__main__':
    main()
