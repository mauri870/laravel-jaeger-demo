<?php

use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    Log::info('Serving welcome page', ['name' => 'welcome']);
    Log::info('A simple laravel log statement', ['url' => request()->getUri()]);
    return view('welcome');
});

Route::get('/error', function () {
    throw new Exception("Something went wrong! That's ok, I'm in the trace now :)");
});

Route::get('/really-long-request', function () {
    Log::info("Processing...");
    sleep(2);
    Log::info("Loong 2s response time :/");
});
