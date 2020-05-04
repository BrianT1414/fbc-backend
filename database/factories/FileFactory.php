<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\File;
use Faker\Generator as Faker;

$factory->define(File::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'path' => '',
        'name' => $faker->word.'.png',
        'type' => 'img',
        'user_id' => factory(User::class)->create()->id
    ];
});
