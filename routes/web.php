<?php


use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});


Route::get('/audios', function () {
    return view('audios/index');
});