<?php

use App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => ['auth:api']],function(){
	Route::get('/ping',fn() => "pong");
	Route::get('/user', function (Request $request) {return $request->user();});
});

Route::post('/login',[Auth\AuthenticatedSessionController::class,'store'])->name('login.post');