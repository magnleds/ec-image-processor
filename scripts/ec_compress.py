from PIL import Image
import os, sys, argparse, oxipng

EXTS = ('jpg', 'jpeg', 'png')

def collect_files(input_dir, output_dir, recursive):
    files = []
    if recursive:
        for root, dirs, fnames in os.walk(input_dir):
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
    p.add_argument('--jpg-quality', type=int, default=85)
    p.add_argument('--png-level', type=int, default=4)
    p.add_argument('--recursive', action='store_true')
    args = p.parse_args()

    INPUT  = os.path.expanduser(args.input)
    OUTPUT = os.path.expanduser(args.output) if args.output else os.path.join(INPUT, 'output')

    if not os.path.isdir(INPUT):
        print(f'错误：目录不存在 → {INPUT}', flush=True)
        sys.exit(1)

    os.makedirs(OUTPUT, exist_ok=True)
    files = collect_files(INPUT, OUTPUT, args.recursive)
    total = len(files)

    if not total:
        print('没有找到图片（jpg/jpeg/png）', flush=True)
        sys.exit(0)

    print(f'找到 {total} 张图片，输出到 {OUTPUT}', flush=True)
    saved_total = 0

    for i, src in enumerate(files, 1):
        fname = os.path.relpath(src, INPUT)
        orig_kb = os.path.getsize(src) // 1024
        ext = os.path.basename(src).lower().rsplit('.', 1)[-1]
        try:
            img = Image.open(src).convert('RGB')

            rel_dir = os.path.dirname(fname)
            out_dir = os.path.join(OUTPUT, rel_dir) if rel_dir else OUTPUT
            os.makedirs(out_dir, exist_ok=True)

            base = os.path.basename(fname)
            out_name = os.path.splitext(base)[0] + ('.png' if ext == 'png' else '.jpg')
            out_path = os.path.join(out_dir, out_name)

            if ext == 'png':
                img.save(out_path, 'PNG')
                oxipng.optimize(out_path, level=args.png_level)
            else:
                img.save(out_path, 'JPEG', quality=args.jpg_quality, optimize=True)

            out_kb = os.path.getsize(out_path) // 1024
            saved  = orig_kb - out_kb
            saved_total += saved
            ratio  = int((1 - out_kb / max(orig_kb, 1)) * 100)
            print(f'[{i}/{total}] {fname}  {orig_kb}KB → {out_kb}KB  (-{ratio}%)', flush=True)
        except Exception as e:
            print(f'[{i}/{total}] {fname} 失败: {e}', flush=True)

    print(f'完成！共节省 {saved_total}KB', flush=True)

if __name__ == '__main__':
    main()
