<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\YoutubeVideo;
use Faker\Generator as Faker;

$factory->define(YoutubeVideo::class, function (Faker $faker) {
    return [
        'youtube_id' => 'oHg5SJYRHA0',
        'user_id' => factory(User::class)->create()->id
    ];
});
