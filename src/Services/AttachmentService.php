<?php

namespace mradang\LaravelAttachment\Services;

use mradang\LaravelAttachment\Models\Attachment;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use mradang\LaravelAttachment\Jobs\MakeThumbnail;

class AttachmentService
{
    public static function createByFile($class, $key, $file, $data)
    {
        return self::create($class, $key, $file, $data);
    }

    public static function createByUrl($class, $key, $url, $data, $allow_exts = ['jpg', 'jpeg', 'png'])
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'laravel-attachment');
        $client = new Client();
        $client->request('GET', $url, ['sink' => $temp_file]);

        $file = new File($temp_file);
        if (!in_array($file->guessExtension(), $allow_exts)) {
            return false;
        }

        return self::create($class, $key, $file, $data);
    }

    private static function create($class, $key, $file, $data)
    {
        $directory = \rtrim(config('attachment.directory'), '/') . '/'
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

    // 你可以使用 url 方法来获取给定文件的 URL。
    // 如果你使用的是 local 驱动，一般只是在给定的路径上加上 /storage 并返回一个相对的 URL 到那个文件。
    // 如果使用的是 s3 或者是 rackspace 驱动，会返回完整的远程 URL：
    //
    // 注意：切记，如果使用的是 local 驱动，则所有想被公开访问的文件都应该放在 storage/app/public 目录下。
    // 此外你应该在 public/storage 创建一个符号链接 来指向 storage/app/public 目录。
    public static function url($class, $key, $id)
    {
        $attachment = Attachment::where([
            'id' => $id,
            'attachmentable_id' => $key,
            'attachmentable_type' => $class,
        ])->firstOrFail();

        return Storage::disk(config('attachment.disk'))->url($attachment->filename);
    }

    public static function download($class, $key, $id)
    {
        $attachment = Attachment::where([
            'id' => $id,
            'attachmentable_id' => $key,
            'attachmentable_type' => $class,
        ])->firstOrFail();

        return Storage::disk(config('attachment.disk'))->download($attachment->filename);
    }

    public static function showImage($class, $key, int $id, int $width, int $height)
    {
        $attachment = Attachment::where([
            'id' => $id,
            'attachmentable_id' => $key,
            'attachmentable_type' => $class,
        ])->firstOrFail();

        if (empty($attachment->imageInfo)) {
            abort('500', 'Not a picture.');
        }

        if (!$width || !$height) {
            return Storage::disk(config('attachment.disk'))->download($attachment->filename);
        }

        $thumb = self::generateThumbName($attachment->filename, $width, $height);
        if (!Storage::disk(config('attachment.disk'))->exists($thumb)) {
            dispatch(new MakeThumbnail($attachment->filename, $width, $height));
            return Storage::disk(config('attachment.disk'))->download($attachment->filename);
        }

        return Storage::disk(config('attachment.disk'))->download($thumb);
    }

    // 生成图片缩略图
    public static function makeThumb(string $storage_filename, int $width, int $height)
    {
        $storagePath = Storage::disk(config('attachment.disk'))->getDriver()->getAdapter()->getPathPrefix();
        $pathname = $storagePath . $storage_filename;

        if (!is_file($pathname)) {
            return false;
        }

        $imagesize = @getimagesize($pathname);
        if (!is_array($imagesize)) {
            return false;
        }

        $thumb_full = $storagePath . self::generateThumbName($storage_filename, $width, $height);

        if (!is_file($thumb_full)) {
            $path = dirname($thumb_full);
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $image = new \Gumlet\ImageResize($pathname);
            $image->resizeToBestFit($width, $height);
            $image->save($thumb_full, \IMAGETYPE_JPEG);
        }
    }

    // 生成缩略图存储文件名
    private static function generateThumbName(string $storage_filename, int $width, int $height)
    {
        return sprintf(
            '%s/%s_%dx%d.jpg',
            \rtrim(config('attachment.thumbnail'), '/'),
            $storage_filename,
            $width,
            $height
        );
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
