from PIL import Image
import os, sys, argparse

EXTS = ('jpg', 'jpeg', 'png', 'webp', 'bmp', 'gif', 'tiff')

def parse_color(s):
    parts = [int(x) for x in s.split(',')]
    return tuple(parts) if len(parts) == 3 else (255, 255, 255)

def collect_files(input_dir, output_dir, recursive):
    files = []
    if recursive:
        for root, dirs, fnames in os.walk(input_dir):
            # 跳过输出目录
            dirs[:] = [d for d in dirs if os.path.normpath(os.path.join(root, d)) != os.path.normpath(output_dir)]
            for f in fnames:
                if not f.startswith('.') and f.lower().rsplit('.', 1)[-1] in EXTS:
                    files.append(os.path.join(root, f))
    else:
        for f in os.listdir(input_dir):
            full = os.path.join(input_dir, f)
            if (not f.startswith('.')
                    and os.path.isfile(full)
                    and os.path.normpath(full) != os.path.normpath(output_dir)
                    and f.lower().rsplit('.', 1)[-1] in EXTS):
                files.append(full)
    return files

def main():
    p = argparse.ArgumentParser()
    p.add_argument('input')
    p.add_argument('output', nargs='?', default='')
    p.add_argument('--size', type=int, default=800)
    p.add_argument('--bg', default='255,255,255')
    p.add_argument('--recursive', action='store_true')
    args = p.parse_args()

    INPUT  = os.path.expanduser(args.input)
    OUTPUT = os.path.expanduser(args.output) if args.output else os.path.join(INPUT, 'output')
    SIZE   = (args.size, args.size)
    BG     = parse_color(args.bg)

    if not os.path.isdir(INPUT):
        print(f'错误：目录不存在 → {INPUT}', flush=True)
        sys.exit(1)

    os.makedirs(OUTPUT, exist_ok=True)
    files = collect_files(INPUT, OUTPUT, args.recursive)
    total = len(files)

    if not total:
        print('没有找到图片', flush=True)
        sys.exit(0)

    print(f'找到 {total} 张图片，输出到 {OUTPUT}', flush=True)

    for i, src in enumerate(files, 1):
        fname = os.path.relpath(src, INPUT)
        try:
            img = Image.open(src)
            if img.mode in ('RGBA', 'LA') or (img.mode == 'P' and 'transparency' in img.info):
                bg = Image.new('RGB', img.size, BG)
                if img.mode == 'P':
                    img = img.convert('RGBA')
                bg.paste(img, mask=img.split()[-1] if img.mode == 'RGBA' else None)
                img = bg
            else:
                img = img.convert('RGB')

            img.thumbnail(SIZE, Image.LANCZOS)
            canvas = Image.new('RGB', SIZE, BG)
            offset = ((SIZE[0] - img.width) // 2, (SIZE[1] - img.height) // 2)
            canvas.paste(img, offset)

            # 保持子目录结构
            rel_dir = os.path.dirname(fname)
            out_dir = os.path.join(OUTPUT, rel_dir) if rel_dir else OUTPUT
            os.makedirs(out_dir, exist_ok=True)

            base = os.path.basename(fname)
            ext  = base.lower().rsplit('.', 1)[-1]
            out_name = os.path.splitext(base)[0] + ('.png' if ext == 'png' else '.jpg')
            out_path = os.path.join(out_dir, out_name)

            if ext == 'png':
                canvas.save(out_path, 'PNG')
            else:
                canvas.save(out_path, 'JPEG', quality=95)

            print(f'[{i}/{total}] {fname} → {out_name}', flush=True)
        except Exception as e:
            print(f'[{i}/{total}] {fname} 失败: {e}', flush=True)

    print(f'完成！共处理 {total} 张', flush=True)

if __name__ == '__main__':
    main()
