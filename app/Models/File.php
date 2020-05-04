<?php

namespace App\Models;

use Storage;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    public $guarded = [];

    protected $appends = ['public_path'];

    public function getPublicPathAttribute()
    {
        return Storage::disk($this->driver)->url($this->path . $this->name);
    }
}
