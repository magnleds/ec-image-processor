from PIL import Image
import os, sys, oxipng

EXTS = ('jpg', 'jpeg', 'png')

def main():
    if len(sys.argv) < 2:
        print('用法: python3 ec_compress.py <输入目录> [输出目录]')
        sys.exit(1)

    INPUT  = os.path.expanduser(sys.argv[1])
    OUTPUT = os.path.expanduser(sys.argv[2]) if len(sys.argv) > 2 else os.path.join(INPUT, 'output')

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
                oxipng.optimize(out_path, level=4)
            else:
                img.save(out_path, 'JPEG', quality=85, optimize=True)

            out_kb = os.path.getsize(out_path) // 1024
            saved  = orig_kb - out_kb
            saved_total += saved
            ratio  = int((1 - out_kb / max(orig_kb, 1)) * 100)
            print(f'[{i}/{total}] {fname}  {orig_kb}KB → {out_kb}KB  (-{ratio}%)', flush=True)
        except Exception as e:
            print(f'[{i}/{total}] {fname} 失败: {e}', flush=True)

    print(f'\n完成！共节省 {saved_total}KB', flush=True)

if __name__ == '__main__':
    main()
