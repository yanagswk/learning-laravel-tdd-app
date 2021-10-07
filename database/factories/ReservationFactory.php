<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\Reservation;
use App\Models\Lesson;
use App\Models\User;

$factory->define(Reservation::class, function (Faker $faker) {
    return [
        "lesson_id" => null,
        "user_id" => null,
    ];
});


