<?php

namespace mradang\LaravelAttachment\Traits;

use Illuminate\Http\UploadedFile;
use mradang\LaravelAttachment\Services\AttachmentService;

trait AttachmentTrait
{
    public function attachments()
    {
        return $this->morphMany('mradang\LaravelAttachment\Models\Attachment', 'attachmentable')->orderBy('sort');
    }

    public function attachmentAddByFile(UploadedFile $file, array $data = [])
    {
        return AttachmentService::createByFile(__CLASS__, $this->getKey(), $file, $data);
    }

    public function attachmentAddByUrl(string $url, array $data = [])
    {
        return AttachmentService::createByUrl(__CLASS__, $this->getKey(), $url, $data);
    }

    public function attachmentSort(array $data)
    {
        return AttachmentService::saveSort(__CLASS__, $this->getKey(), $data);
    }

    public function attachmentDelete($id)
    {
        return AttachmentService::delete(__CLASS__, $this->getKey(), $id);
    }

    public function attachmentClear()
    {
        return AttachmentService::clear(__CLASS__, $this->getKey());
    }

    protected static function bootAttachmentTrait()
    {
        static::deleting(function ($model) {
            $model->attachmentClear();
        });
    }
}
