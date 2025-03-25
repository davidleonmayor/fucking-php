<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AudioController;
use App\Http\Controllers\Api\PodcastController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\GenreController;

use App\Http\Controllers\Api\AlbumController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\HistoryController;
// auth
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//->names('api.v1.genres')
//http://tranquilidad.test/v1/genres


// Rutas para Auth
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

// Ruta para recuperación de contraseña
Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);

// Rutas para Audio
Route::apiResource('audios', AudioController::class);



// Rutas para Playlist
Route::apiResource('playlists', PlaylistController::class);

// Rutas para Genre
Route::apiResource('genres', GenreController::class);



// Rutas para Album
//http://tranquilidad.test/v1/albums
Route::apiResource('albums', AlbumController::class);

// Rutas para Tag
Route::apiResource('tags', TagController::class);

// Rutas para Like
Route::apiResource('likes', LikeController::class);



// Rutas para History
Route::apiResource('histories', HistoryController::class);



// RUTAS PARA ELIMINAR REGISTROS DE LA RELACION DE PLAYLIST
//Route::delete('/playlists/{playlist}/audios/{audio}', [PlaylistController::class, 'removeAudio']);
Route::delete('/playlists/{playlist}/podcasts/{podcast}', [PlaylistController::class, 'removePodcast']);


// Rutas para asociar y desasociar tags con audios y podcasts
// Route::post('/audios/{audio}/tags', [TagController::class, 'attachTagToAudio']);
// Route::delete('/audios/{audio}/tags', [TagController::class, 'detachTagFromAudio']);

Route::post('/podcasts/{podcast}/tags', [TagController::class, 'attachTagToPodcast']);
Route::delete('/podcasts/{podcast}/tags', [TagController::class, 'detachTagFromPodcast']);
