<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// The Ekdanta site's content from the design files (media, collections and,
// as the build goes on, the rest). Safe to run again: see EkdantaSiteSeeder.
Artisan::command('ekdanta:seed', function () {
    $seeder = app(\Database\Seeders\EkdantaSiteSeeder::class);
    $seeder->setCommand($this);
    $seeder->run();
    $this->info('Ekdanta content is in place.');
})->purpose('Seed the Ekdanta site content (media, collections) from the design files');
