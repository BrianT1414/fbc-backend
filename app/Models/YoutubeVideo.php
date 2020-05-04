<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YoutubeVideo extends Model
{
    public $guarded = [];

    public function getWatchAttribute()
    {
        return "https://www.youtube.com/watch/" . $this->youtube_id;
    }
    
    public function getEmbedAttribute()
    {
        return "https://www.youtube.com/embed/" . $this->youtube_id;
    }
}
