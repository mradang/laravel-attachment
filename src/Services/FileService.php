<?php

namespace mradang\LumenAttachment\Services;

use Illuminate\Http\UploadedFile;

class FileService {

    private static function getFolder($folder) {
        $folder_storage = $folder . '/' . date('Y/m/d/');
        $folder_full = storage_path($folder_storage);

        if (!is_dir($folder_full)) {
            mkdir($folder_full, 0644, true);
        }
        return $folder_storage;
    }

    public static function upload(UploadedFile $file) {
        $folder_storage = self::getFolder(config('attachment.attachment_folder'));
        $file_name = $file->hashName();
        $file->move(storage_path($folder_storage), $file_name);
        return $folder_storage . $file_name;
    }

    public static function getUrl($url, $ext = 'jpg') {
        $folder_storage = self::getFolder(config('attachment.attachment_folder'));
        $file_name = md5($url) . '.' . $ext;
        $file_full = storage_path($folder_storage . $file_name);
        $ret = file_put_contents($file_full, file_get_contents($url));
        return $ret ? $folder_storage . $file_name : false;
    }

    public static function delete($file_storage) {
        $file_full = storage_path($file_storage);
        if (file_exists($file_full)) {
            @unlink($file_full);
        }
        // 删除缩略图，非图片文件没有缩略图，什么也不做
        self::clearThumbs($file_storage);
        return !file_exists($file_full);
    }

    public static function makeThumb($file_storage, $width, $height) {
        if (self::isImage($file_storage)) {
            $thumb_full = storage_path(config('attachment.thumb_folder')."/{$file_storage}_{$width}x{$height}.jpg");
            if (! file_exists($thumb_full)) {
                $folder_full = dirname($thumb_full);
                if (! is_dir($folder_full)) {
                    mkdir($folder_full, 0644, true);
                }
                $image = new \Gumlet\ImageResize(storage_path($file_storage));
                $image->resizeToBestFit($width, $height);
                $image->save($thumb_full, \IMAGETYPE_JPEG);
            }
            return $thumb_full;
        }
    }

    public static function isImage($file_storage) {
        $filename = storage_path($file_storage);
        return @is_array(getimagesize($filename));
    }

    private static function clearThumbs($file_storage) {
        $thumb_full = storage_path(config('attachment.thumb_folder')."/{$file_storage}");
        $pattern = $thumb_full.'_*.jpg';
        if (glob($pattern)) {
            array_map('unlink', glob($pattern));
        }
    }

}
