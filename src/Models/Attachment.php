<?php

namespace mradang\LaravelAttachment\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'imageInfo' => 'array',
        'data' => 'array',
    ];

    protected $hidden = [
        'attachmentable_type',
        'attachmentable_id',
    ];

    public function attachmentable()
    {
        return $this->morphTo();
    }
}
