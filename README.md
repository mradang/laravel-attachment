# lumen attachment

为模型增加附件功能

## 依赖
- gumlet/php-image-resize

## 安装
```
composer require mradang/lumen-attachment
```

## 配置
1. 添加 .env 环境变量，使用默认值时可省略
```
# 附件存储在 storage 下的目录名（默认：attachments）
ATTACHMENT_FOLDER=attachments
# 缩略图存储在 storage 下的目录名（默认：thumbs）
ATTACHMENT_THUMB_FOLDER=thumbs
```

2. 修改 bootstrap\app.php 文件
```php
// 注册 ServiceProvider
$app->register(mradang\LumenAttachment\LumenAttachmentServiceProvider::class);
```

## 使用
1. 模型引入 AttachmentTrait
```php
use mradang\LumenAttachment\Traits\AttachmentTrait;
```
> 增加以下内容：
> - morphMany attachments 附件关联（一对多）
> - mradang\LumenAttachment\Models\Attachment attachmentAddByFile($file, array $data = []) 为模型上传文件附件
> - mradang\LumenAttachment\Models\Attachment attachmentAddByUrl($url, array $data = []) 为模型上传 Url 附件
> - void attachmentDelete($id) 删除模型的指定附件
> - void attachmentClear() 清空模型的全部附件
> - response attachmentDownload($id, $name = '') 下载指定附件
> - response attachmentShowPic($id, $width = 0, $height = 0) 显示指定附件图片
> - mradang\LumenAttachment\Models\Attachment attachmentFind($id) 查找指定附件

## 添加的数据表迁移
- attachments
