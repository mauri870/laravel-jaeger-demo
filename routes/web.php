<?php

use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    Log::info('Serving welcome page', ['name' => 'welcome']);

    Log::info('A simple laravel log statement', ['url' => request()->getUri()]);
    return view('welcome');
});
