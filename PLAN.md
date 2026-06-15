# ec-image-processor — PLAN

## 项目目标

电商产品图批量处理工具，运行在 mac2014 上。
解决阿里巴巴/亚马逊等平台上传要求：统一尺寸、白底、高压缩率。

## 当前版本

v1.1.0

## 功能列表

- [x] 等比缩放至 800×800，不裁剪产品，居中放置
- [x] PNG 透明背景自动补白底
- [x] JPG 高质量压缩（quality=95）
- [x] PNG 二次无损压缩（oxipng level=4）
- [x] 批量处理整个文件夹，跳过 output 子目录和隐藏文件
- [x] 支持命令行参数（`python3 ec_resize.py <输入目录> [输出目录]`），不再写死路径
- [x] 每张完成后打印：原始大小 → 处理后大小 + 压缩率，结尾汇报总节省
- [ ] 支持自定义输出尺寸（`--size 1000`）
- [ ] 支持 WebP 输出格式
- [ ] 支持拖拽文件夹到脚本直接运行（macOS 快捷方式）

## 当前进度

v1.1.0：命令行参数 + 进度输出已完成，可直接在 mac2014 上跑。

## 下一步

1. 验证在 mac2014 Python3 环境下能正常运行（`pip install pillow oxipng`）
2. 按需扩展输出尺寸参数

## 运行环境

- 机器：mac2014（192.168.31.181）
- 路径：`/Users/deffipy/Projects/ec-image-processor/`
- 依赖：`pip install -r requirements.txt`（pillow、oxipng）

## 使用方法

```bash
# 修改脚本顶部 INPUT / OUTPUT 路径后运行
python3 scripts/ec_resize.py
```
