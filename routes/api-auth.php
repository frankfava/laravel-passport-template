<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => 'pong')->name('ping');

Route::get('/user', fn (Request $request) => $request->user())->name('user');
