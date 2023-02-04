# laravel attachment

为模型增加附件功能

## 安装
```
composer require mradang/laravel-attachment
```

## 配置
1. 添加 .env 环境变量，使用默认值时可省略
```
# 附件存储在 storage 下的目录名（默认：attachments）
ATTACHMENT_DIRECTORY=attachments
```

## 添加的内容

### 添加的数据表迁移
- attachments

## 使用

### 模型 Trait

```php
use mradang\LaravelAttachment\Traits\AttachmentTrait;
```

> 增加以下内容：
> - morphMany attachments 附件关联（一对多）
> - mradang\LaravelAttachment\Models\Attachment attachmentAddByFile($file, array $data = []) 为模型上传文件附件
> - mradang\LaravelAttachment\Models\Attachment attachmentAddByUrl($url, array $data = []) 为模型上传 Url 附件
> - void attachmentSort(array $data) 附件排序
> - void attachmentDelete($id) 删除模型的指定附件
> - void attachmentClear() 清空模型的全部附件

#### 模型删除时自动清理附件