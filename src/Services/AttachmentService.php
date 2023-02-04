<?php

namespace mradang\LaravelAttachment\Services;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use mradang\LaravelAttachment\Models\Attachment;

class AttachmentService
{
    public static function createByFile($class, $key, $file, $data)
    {
        return self::create($class, $key, $file, $data);
    }

    public static function createByUrl($class, $key, $url, $data, $allow_exts = ['jpg', 'jpeg', 'png'])
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'laravel-attachment');
        Http::sink($temp_file)->get($url);

        $file = new File($temp_file);
        if (!in_array($file->guessExtension(), $allow_exts)) {
            return false;
        }

        return self::create($class, $key, $file, $data);
    }

    private static function create($class, $key, $file, $data)
    {
        $directory = Str::finish(config('attachment.directory'), '/')
            . \strtolower(class_basename($class)) . '/'
            . $key;

        $imagesize = @getimagesize($file->getPathname());
        $imageInfo = is_array($imagesize) ? ['width' => $imagesize[0], 'height' => $imagesize[1]] : null;

        $filesize = $file->getSize();

        $filename = Storage::disk(config('attachment.disk'))->putFile($directory, $file);

        $attachment = new Attachment([
            'attachmentable_type' => $class,
            'attachmentable_id' => $key,
            'filename' => $filename,
            'filesize' => $filesize,
            'imageInfo' => $imageInfo,
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

    public static function saveSort($class, $key, array $data)
    {
        foreach ($data as $item) {
            Attachment::where([
                'id' => $item['id'],
                'attachmentable_id' => $key,
                'attachmentable_type' => $class,
            ])->update(['sort' => $item['sort']]);
        }
    }

    public static function delete($class, $key, $id)
    {
        $attachment = Attachment::where([
            'id' => $id,
            'attachmentable_id' => $key,
            'attachmentable_type' => $class,
        ])->firstOrFail();

        $ret = Storage::disk(config('attachment.disk'))->delete($attachment->filename);
        if ($ret) {
            $attachment->delete();
        }
    }

    public static function clear($class, $key)
    {
        $attachments = Attachment::where([
            'attachmentable_id' => $key,
            'attachmentable_type' => $class,
        ])->get();
        foreach ($attachments as $attachment) {
            $ret = Storage::disk(config('attachment.disk'))->delete($attachment->filename);
            if ($ret) {
                $attachment->delete();
            }
        }
    }
}
