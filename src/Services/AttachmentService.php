<?php

namespace mradang\LumenAttachment\Services;

use mradang\LumenAttachment\Models\Attachment;

class AttachmentService {

    public static function createByFile($class, $key, $file, $data) {
        $filename = FileService::upload($file);

        if (!$filename) {
            throw new \Exception('上传文件失败！');
        }

        return self::create($class, $key, $filename, $data);
    }

    public static function createByUrl($class, $key, $url, $data) {
        $filename = FileService::getUrl($url);

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
            'data' => $data,
        ]);

        if ($attachment->save()) {
            return $attachment;
        }
    }

    public static function deleteFile($class, $key, $id) {
        $attachment = Attachment::findOrFail($id);
        if ($attachment->attachmentable_id === $key && $attachment->attachmentable_type === $class) {
            if (FileService::delete($attachment->file_name)) {
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
            if (FileService::delete($attachment->file_name)) {
                $attachment->delete();
            }
        }
    }

    public static function download($class, $key, $id, $name) {
        $attachment = Attachment::findOrFail($id);
        $filename = storage_path($attachment->file_name);
        $fileext = pathinfo($filename, PATHINFO_EXTENSION);
        $filebase = $name ?: basename($filename, ".$fileext");
        return response()->download(
            $filename,
            $filebase . ($fileext ? '.' : '') . $fileext,
            ['Cache-Control' => 'max-age=31536000, must-revalidate']
        );
    }

    public static function showPic($class, $key, $id, $width, $height) {
        $attachment = Attachment::findOrFail($id);
        $filename = storage_path($attachment->file_name);

        if (!FileService::isImage($attachment->file_name)) {
            return response('非图片', 400);
        }

        if ($width && $height) {
            $filename = FileService::makeThumb($attachment->file_name, $width, $height);
            if (empty($filename)) {
                return response('生成缩略图失败', 400);
            }
        }

        return response()->download(
            $filename,
            basename($filename),
            ['Cache-Control' => 'max-age=31536000, must-revalidate']
        );
    }

    public static function find($class, $key, $id) {
        return Attachment::where([
            'id' => $id,
            'attachmentable_id' => $key,
            'attachmentable_type' => $class,
        ])->first();
    }

}
