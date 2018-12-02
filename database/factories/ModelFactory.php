<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\FeedItemGame::class, function (Faker\Generator $faker) {
    return [
        //'game_id' => $faker->game_id,
        'source' => 'ModelFactory',
        'item_title' => $faker->text(100),
        'item_genre' => $faker->text(50),
        'item_developers' => $faker->text(50),
        'item_publishers' => $faker->text(50),
        'release_date_eu' => $faker->date($format = 'Y-m-d', $max = 'now'),
        'upcoming_date_eu' => $faker->date($format = 'Y-m-d', $max = 'now'),
        'is_released_eu' => 0,
        'release_date_us' => $faker->date($format = 'Y-m-d', $max = 'now'),
        'upcoming_date_us' => $faker->date($format = 'Y-m-d', $max = 'now'),
        'is_released_us' => 0,
        'release_date_jp' => $faker->date($format = 'Y-m-d', $max = 'now'),
        'upcoming_date_jp' => $faker->date($format = 'Y-m-d', $max = 'now'),
        'is_released_jp' => 0,
        'modified_fields' => [],
        'status_code' => null,
        'status_desc' => null,
    ];
});
