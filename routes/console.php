<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('Practice deliberately; explain every decision.');
})->purpose('Display a practice reminder');
