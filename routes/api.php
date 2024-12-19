<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->group(function () {
    Route::group(['prefix'=> '/auth'], function () {
        Route::get('/protected-resource', [AuthController::class, 'patientHome']);
    });
});

Route::post('/signup', [AuthController::class, 'signUp']);
Route::post('/login', [AuthController::class,'logIn']);