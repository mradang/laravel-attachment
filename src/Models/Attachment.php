<?php

namespace mradang\LumenAttachment\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attachment extends Model {

    protected $fillable = [
        'attachmentable_type',
        'attachmentable_id',
        'file_name',
        'file_size',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected $hidden = [
        'attachmentable_type',
        'attachmentable_id',
    ];

    public function attachmentable() {
        return $this->morphTo();
    }

}
