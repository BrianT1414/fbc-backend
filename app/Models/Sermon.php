<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\File;
use App\Models\YoutubeVideo;
use Illuminate\Database\Eloquent\Model;

class Sermon extends Model
{
    public $guarded = [];

    protected $appends = ['video'];
    protected $with = ['audioFile'];

    public function audioFile()
    {
        return $this->belongsTo(File::class, 'audio_file_id');
    }

    public function getIsPublishedAttribute()
    {
        if (Carbon::createFromFormat('Y-m-d H:i:s', $this->published_on, new \DateTimeZone('America/Chicago'))->isPast()) {
            return true;
        }

        return false;
    }

    public function getVideoAttribute()
    {
        if ($this->video_type === 'youtube') {
            return YoutubeVideo::where('id', $this->video_id)->first();
        } else if ($this->video_type === 'local') {
            return File::where('id', $this->video_id)->first();
        }

        return null;
    }

    public function scopePublic($query)
    {
        return $query->where('published_on', '<', (new \DateTime("now", new \DateTimeZone('America/Chicago')))->format('Y-m-d H:i:s'));
    }
}
