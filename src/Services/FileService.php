<?php

namespace mradang\LaravelAttachment\Services;

use Gumlet\ImageResize;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileService
{
    // 上传文件
    public static function uploadFile(UploadedFile $file)
    {
        $folder_storage = self::attachmentTodayFolder();
        $file_name = md5($file->path() . time()) . '.' . $file->getClientOriginalExtension();
        $file->move(storage_path($folder_storage), $file_name);
        return is_file(storage_path($folder_storage . $file_name))
            ? $folder_storage . $file_name
            : false;
    }

    // 上传 Url
    public static function uploadUrl(string $url)
    {
        $folder_storage = self::attachmentTodayFolder();
        $parsed_url = parse_url($url);
        if (!is_array($parsed_url)) {
            return false;
        }
        $file_ext = pathinfo($parsed_url['path'], PATHINFO_EXTENSION);
        $file_ext = ($file_ext ? '.' : '') . $file_ext;
        $file_name = md5($url . time()) . $file_ext;
        $file_full = storage_path($folder_storage . $file_name);
        $ret = file_put_contents($file_full, file_get_contents($url));
        return $ret
            ? $folder_storage . $file_name
            : false;
    }

    // 生成图片缩略图
    public static function makeThumb(string $file_storage, int $width, int $height)
    {
        if (!self::isImage($file_storage)) {
            return false;
        }
        if ($width < 0 || $height < 0) {
            return false;
        }
        $thumb_storage = self::generateThumbName($file_storage, $width, $height);
        $thumb_full = storage_path($thumb_storage);
        if (!is_file($thumb_full)) {
            self::ensureFolderExists(dirname($thumb_full));
            $image = new ImageResize(storage_path($file_storage));
            $image->resizeToBestFit($width, $height);
            $image->save($thumb_full, \IMAGETYPE_JPEG);
        }
        return $thumb_storage;
    }

    // 生成缩略图文件名
    public static function generateThumbName(string $file_storage, int $width, int $height)
    {
        return self::thumbFolder() . "{$file_storage}_{$width}x{$height}.jpg";
    }

    // 删除文件
    public static function deleteFile($file_storage)
    {
        $file_full = storage_path($file_storage);
        if (is_file($file_full)) {
            self::clearThumbs($file_storage);
            @unlink($file_full);
        }
        return !is_file($file_full);
    }

    // - 当日存储路径
    private static function attachmentTodayFolder()
    {
        $folder_storage = Str::finish(config('attachment.attachment_folder'), '/') . date('Y/m/d/');
        self::ensureFolderExists(storage_path($folder_storage));
        return $folder_storage;
    }

    // - 缩略图目录
    private static function thumbFolder()
    {
        return Str::finish(config('attachment.thumb_folder'), '/');
    }

    // - 清理指定文件的缩略图
    private static function clearThumbs(string $file_storage)
    {
        $thumb_full = storage_path(self::thumbFolder() . $file_storage);
        $pattern = $thumb_full . '_*.jpg';
        if (glob($pattern)) {
            array_map('unlink', glob($pattern));
        }
    }

    // - 确保目录存在
    private static function ensureFolderExists(string $path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    // - 文件是否图片
    private static function isImage(string $file_storage)
    {
        $file_full = storage_path($file_storage);
        return @is_array(getimagesize($file_full));
    }
}
