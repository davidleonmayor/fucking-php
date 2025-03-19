<?php


use Illuminate\Support\Facades\Route;

Route::get('/audios', function () {
    return view('audios.index');
});

Route::get('/audios/show', function () {
    return view('audios.show');
});

// Otro ejemplo para "genres"
Route::get('/genres', function () {
    return view('genres.index');
});
