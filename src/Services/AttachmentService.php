<?php

namespace mradang\LumenAttachment\Services;

use mradang\LumenAttachment\Models\Attachment;
use mradang\LumenFile\Services\FileService;

class AttachmentService {

    public static function createByFile($class, $key, $file, $data) {
        $filename = FileService::uploadFile($file);

        if (!$filename) {
            throw new \Exception('上传文件失败！');
        }

        return self::create($class, $key, $filename, $data);
    }

    public static function createByUrl($class, $key, $url, $data) {
        $filename = FileService::uploadUrl($url);

        if (!$filename) {
            throw new \Exception('获取远程文件失败！');
        }

        return self::create($class, $key, $filename, $data);
    }

    public static function create($class, $key, $filename, $data) {
        $attachment = new Attachment([
            'attachmentable_type' => $class,
            'attachmentable_id' => $key,
            'file_name' => $filename,
            'file_size' => filesize(storage_path($filename)),
            'sort' => Attachment::where([
                'attachmentable_id' => $key,
                'attachmentable_type' => $class,
            ])->max('sort') + 1,
            'data' => $data,
        ]);

        if ($attachment->save()) {
            return $attachment;
        }
    }

    public static function deleteFile($class, $key, $id) {
        $attachment = Attachment::findOrFail($id);
        if ($attachment->attachmentable_id === $key && $attachment->attachmentable_type === $class) {
            if (FileService::deleteFile($attachment->file_name)) {
                $attachment->delete();
            }
        }
    }

    public static function clear($class, $key) {
        $attachments = Attachment::where([
            'attachmentable_id' => $key,
            'attachmentable_type' => $class,
        ])->get();
        foreach ($attachments as $attachment) {
            if (FileService::deleteFile($attachment->file_name)) {
                $attachment->delete();
            }
        }
    }

    public static function download($class, $key, $id, $name) {
        $attachment = Attachment::findOrFail($id);
        $filename = $attachment->file_name;
        return FileService::response($filename, $name);
    }

    public static function showPic($class, $key, $id, $width, $height) {
        $attachment = Attachment::findOrFail($id);
        $filename = $attachment->file_name;

        if (!FileService::isImage($filename)) {
            return response('非图片', 400);
        }

        if ($width && $height) {
            $filename = FileService::makeThumb($filename, $width, $height);
            if (empty($filename)) {
                return response('生成缩略图失败', 400);
            }
        }

        return FileService::showImage($filename, $width, $height);
    }

    public static function find($class, $key, $id) {
        return Attachment::where([
            'id' => $id,
            'attachmentable_id' => $key,
            'attachmentable_type' => $class,
        ])->first();
    }

    public static function saveSort($class, $key, array $data) {
        foreach ($data as $item) {
            Attachment::where([
                'id' => $item['id'],
                'attachmentable_id' => $key,
                'attachmentable_type' => $class,
            ])->update(['sort' => $item['sort']]);
        }
    }

}
