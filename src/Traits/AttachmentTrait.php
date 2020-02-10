<?php

namespace mradang\LaravelAttachment\Traits;

use mradang\LaravelAttachment\Services\AttachmentService;

trait AttachmentTrait
{

    public function attachments()
    {
        return $this->morphMany('mradang\LaravelAttachment\Models\Attachment', 'attachmentable')->orderBy('sort');
    }

    public function attachmentAddByFile($file, array $data = [])
    {
        return AttachmentService::createByFile(__CLASS__, $this->getKey(), $file, $data);
    }

    public function attachmentAddByUrl($url, array $data = [])
    {
        return AttachmentService::createByUrl(__CLASS__, $this->getKey(), $url, $data);
    }

    public function attachmentDelete($id)
    {
        return AttachmentService::deleteFile(__CLASS__, $this->getKey(), $id);
    }

    public function attachmentClear()
    {
        return AttachmentService::clear(__CLASS__, $this->getKey());
    }

    public function attachmentDownload($id)
    {
        return AttachmentService::download(__CLASS__, $this->getKey(), $id);
    }

    public function attachmentShowImage($id, $width = 0, $height = 0)
    {
        return AttachmentService::showImage(__CLASS__, $this->getKey(), $id, $width, $height);
    }

    public function attachmentFind($id)
    {
        return AttachmentService::find(__CLASS__, $this->getKey(), $id);
    }

    public function attachmentSort(array $data)
    {
        return AttachmentService::saveSort(__CLASS__, $this->getKey(), $data);
    }
}
