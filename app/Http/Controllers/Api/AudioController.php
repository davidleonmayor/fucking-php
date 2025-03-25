<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Audio;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AudioController extends Controller
{
    // Listar audios con filtrado por álbum
    public function index(Request $request)
    {
        $query = Audio::query();

        if ($request->has('album_id')) {
            $query->where('album_id', $request->album_id);
        }

        $audios = $query->get(); // Puedes cambiar a paginate() si necesitas paginación

        return response()->json($audios);
    }

    // Crear un nuevo audio
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'audio_file' => 'required|mimes:mp3,wav,aac|max:10000|unique:audios,audio_file',
            'duration' => 'required|integer',
            'genre_id' => 'required|exists:genres,id',
            'album_id' => 'nullable|exists:albums,id',
            'es_binaural' => 'required|boolean',
            'frecuencia' => 'required_if:es_binaural,true|nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            $imageFilePath = $request->hasFile('image_file')
                ? Cloudinary::upload($request->file('image_file')->getRealPath(), ['folder' => 'audios/images'])->getSecurePath()
                : null;

            $audioFilePath = Cloudinary::upload($request->file('audio_file')->getRealPath(), [
                'resource_type' => 'video',
                'folder' => 'audios/mp3'
            ])->getSecurePath();

            $audio = Audio::create(array_merge($request->only([
                'title', 'description', 'duration', 'genre_id', 'album_id', 'es_binaural', 'frecuencia'
            ]), [
                'image_file' => $imageFilePath,
                'audio_file' => $audioFilePath,
            ]));

            DB::commit();

            return response()->json($audio, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear el audio: ' . $e->getMessage()], 400);
        }
    }

    // Mostrar un audio específico
    public function show($id)
    {
        $audio = Audio::with(['genre', 'album', 'tags', 'likes', 'histories', 'playlists'])->findOrFail($id);
        return response()->json($audio);
    }

    // Actualizar un audio existente
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'image_file' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'audio_file' => 'sometimes|nullable|mimes:mp3,wav,aac|max:10000|unique:audios,audio_file,' . $id,
            'duration' => 'sometimes|required|integer',
            'genre_id' => 'sometimes|required|exists:genres,id',
            'album_id' => 'sometimes|nullable|exists:albums,id',
            'es_binaural' => 'sometimes|required|boolean',
            'frecuencia' => 'sometimes|required_if:es_binaural,true|nullable|numeric',
        ]);

        $audio = Audio::findOrFail($id);
        
        if ($request->hasFile('image_file')) {
            $audio->image_file = Cloudinary::upload($request->file('image_file')->getRealPath(), ['folder' => 'images'])->getSecurePath();
        }

        if ($request->hasFile('audio_file')) {
            $audio->audio_file = Cloudinary::upload($request->file('audio_file')->getRealPath(), ['resource_type' => 'video', 'folder' => 'audios'])->getSecurePath();
        }

        $audio->update($request->except(['image_file', 'audio_file']));
        return response()->json($audio, 200);
    }

    // Eliminar un audio
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $audio = Audio::findOrFail($id);
            if ($audio->image_file) Cloudinary::destroy('images/' . pathinfo($audio->image_file, PATHINFO_FILENAME));
            if ($audio->audio_file) Cloudinary::destroy('audios/' . pathinfo($audio->audio_file, PATHINFO_FILENAME), ['resource_type' => 'video']);
            $audio->delete();
            DB::commit();
            return response()->json(['message' => 'Audio eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el audio: ' . $e->getMessage()], 400);
        }
    }
}
