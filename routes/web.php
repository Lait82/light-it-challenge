<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/db-test', function () {
    try {
        \DB::connection()->getPdo();
        return "Connected to database: " . \DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        return "Connection error: " . $e->getMessage();
    }
});
