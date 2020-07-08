<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use mradang\LaravelAttachment\Traits\AttachmentTrait;

class User extends Model
{
    use AttachmentTrait;

    protected $fillable = ['name'];
}
