<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\File;
use App\Models\Sermon;
use App\Models\YoutubeVideo;
use Faker\Generator as Faker;

$factory->define(Sermon::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'description' => $faker->sentence,
        'video_type' => 'youtube',
        'video_id' => factory(YoutubeVideo::class)->create()->id,
        'published_on' => $faker->date('Y-m-d H:i:s'),
        'audio_file_id' => factory(File::class)->create()->id,
        'author_id' => factory(User::class)->create()->id
    ];
});
