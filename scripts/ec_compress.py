from PIL import Image
import os, sys, argparse, oxipng

EXTS = ('jpg', 'jpeg', 'png')

def main():
    p = argparse.ArgumentParser()
    p.add_argument('input')
    p.add_argument('output', nargs='?', default='')
    p.add_argument('--jpg-quality', type=int, default=85)
    p.add_argument('--png-level', type=int, default=4)
    args = p.parse_args()

    INPUT  = os.path.expanduser(args.input)
    OUTPUT = os.path.expanduser(args.output) if args.output else os.path.join(INPUT, 'output')

    if not os.path.isdir(INPUT):
        print(f'错误：目录不存在 → {INPUT}', flush=True)
        sys.exit(1)

    os.makedirs(OUTPUT, exist_ok=True)

    fnames = [f for f in os.listdir(INPUT)
              if not f.startswith('.')
              and os.path.isfile(os.path.join(INPUT, f))
              and os.path.normpath(os.path.join(INPUT, f)) != os.path.normpath(OUTPUT)
              and f.lower().rsplit('.', 1)[-1] in EXTS]

    total = len(fnames)
    if not total:
        print('没有找到图片（jpg/jpeg/png）', flush=True)
        sys.exit(0)

    print(f'找到 {total} 张图片，输出到 {OUTPUT}', flush=True)
    saved_total = 0

    for i, fname in enumerate(fnames, 1):
        src = os.path.join(INPUT, fname)
        ext = fname.lower().rsplit('.', 1)[-1]
        orig_kb = os.path.getsize(src) // 1024
        try:
            img = Image.open(src).convert('RGB')
            out_name = os.path.splitext(fname)[0] + ('.png' if ext == 'png' else '.jpg')
            out_path = os.path.join(OUTPUT, out_name)

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
