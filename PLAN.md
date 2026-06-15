# ec-image-processor — PLAN

## 项目目标

电商产品图批量处理工具，运行在 mac2014 上。
解决阿里巴巴/亚马逊等平台上传要求：统一尺寸、白底、高压缩率。

## 当前版本

v1.0.0

## 功能列表

- [x] 等比缩放至 800×800，不裁剪产品，居中放置
- [x] PNG 透明背景自动补白底
- [x] JPG 高质量压缩（quality=95）
- [x] PNG 二次无损压缩（oxipng level=4）
- [x] 批量处理整个文件夹，跳过 output 子目录和隐藏文件
- [ ] 支持自定义输出尺寸（命令行参数）
- [ ] 支持 WebP 输出格式
- [ ] 处理进度条 + 完成汇报（当前文件 / 总文件数、压缩率）
- [ ] 支持拖拽文件夹到脚本直接运行（macOS 快捷方式）

## 当前进度

v1.0.0 核心功能完整，路径写死在脚本顶部，需要手动改 INPUT/OUTPUT 再跑。

## 下一步

1. 路径改为命令行参数（`python3 ec_resize.py <input_dir> [output_dir]`），不再写死
2. 加处理进度输出（每张完成后打印文件名 + 压缩率）
3. 验证在 mac2014 Python3 环境下能正常运行（pip install pillow oxipng）

## 运行环境

- 机器：mac2014（192.168.31.181）
- 路径：`/Users/deffipy/Projects/ec-image-processor/`
- 依赖：`pip install -r requirements.txt`（pillow、oxipng）

## 使用方法

```bash
# 修改脚本顶部 INPUT / OUTPUT 路径后运行
python3 scripts/ec_resize.py
```
