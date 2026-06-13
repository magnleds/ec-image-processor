# ec-image-processor

电商图片批量处理工具，基于 Python + Pillow + oxipng。

## 功能

- 等比缩放至 800×800，不裁剪产品
- PNG 透明背景自动补白底
- JPG 高质量压缩（quality=95）
- PNG 二次无损压缩（oxipng level=4）

## 环境

```bash
pip install -r requirements.txt
```

## 使用

修改 `scripts/ec_resize.py` 顶部的 `INPUT` 和 `OUTPUT` 路径，然后：

```bash
python3 scripts/ec_resize.py
```

## 压缩效果参考

| 文件 | 原图 | 处理后 | 压缩率 |
|------|------|--------|--------|
| 产品 JPG | 515KB | 16KB | -97% |
| 微信大图 JPG | 19MB | 57KB | -99.7% |
| 透明 PNG | 1.5MB | 51KB | -97% |
