<?php

return [

    /*
     * The disk name to store file, the value is key of `disks` in `config/filesystems.php`
     */
    'disk' => env('ATTACHMENT_DISK', 'local'),

    /*
     * Default directory.
     */
    'directory' => env('ATTACHMENT_DIRECTORY', 'attachments'),

    /*
     * Thumbnail directory.
     */
    'thumbnail' => env('ATTACHMENT_THUMBNAIL', 'thumbnails'),

];
