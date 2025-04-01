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
    // Listar audios con filtrado por álbum o género
    public function index(Request $request)
    {
        $query = Audio::query();

        // Filtrar por album_id si está presente
        if ($request->has('album_id')) {
            $query->where('album_id', $request->album_id);
        }

        // Filtrar por genre_id si está presente
        if ($request->has('genre_id')) {
            $query->where('genre_id', $request->genre_id);
        }

        // Filtrar por es_binaural si está presente
        if ($request->has('es_binaural')) {
            $query->where('es_binaural', $request->es_binaural);
        }

        // Filtrar por título si está presente
        if ($request->has('title')) {
            $query->where('title', 'LIKE', '%' . $request->title . '%');
        }

        // Incluye la relación 'genre' y 'album'
        $audios = $query->with(['genre', 'album'])->get();

        return response()->json($audios);
    }
    // public function index(Request $request)
    // {
    //     $query = Audio::query();

    //     // Filtrar por album_id si está presente
    //     if ($request->has('album_id')) {
    //         $query->where('album_id', $request->album_id);
    //     }

    //     // Filtrar por genre_id si está presente
    //     if ($request->has('genre_id')) {
    //         $query->where('genre_id', $request->genre_id);
    //     }

    //     // Filtrar por es_binaural si está presente
    //     if ($request->has('es_binaural')) {
    //         $query->where('es_binaural', $request->es_binaural);
    //     }

    //     // Incluye la relación 'genre' y 'album'
    //     $audios = $query->with(['genre', 'album'])->get();

    //     return response()->json($audios);
    // }

    // Crear un nuevo audio
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image_file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'audio_file' => 'required|mimes:mp3,wav,aac|max:10000|unique:audios,audio_file',
            'duration' => 'required|integer',
            'genre_id' => 'required|exists:genres,id',
            'album_id' => 'nullable|exists:albums,id',
            'es_binaural' => 'required|boolean',
            'frecuencia' => 'required_if:es_binaural,true|nullable|numeric',
        ], [
            'title.required' => 'El título es obligatorio.',
            'title.string' => 'El título debe ser una cadena de texto.',
            'title.max' => 'El título no puede exceder los 255 caracteres.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.required' => 'La descripción es obligatoria.',
            'image_file.required' => 'El archivo de imagen es obligatorio.',
            'image_file.image' => 'El archivo de imagen debe ser una imagen válida.',
            'image_file.mimes' => 'La imagen debe estar en formato jpeg, png, jpg o gif.',
            'image_file.max' => 'La imagen no puede exceder los 2MB.',
            'audio_file.required' => 'El archivo de audio es obligatorio.',
            'audio_file.mimes' => 'El audio debe estar en formato mp3, wav o aac.',
            'audio_file.max' => 'El audio no puede exceder los 10MB.',
            'audio_file.unique' => 'Este archivo de audio ya existe en la base de datos.',
            'duration.required' => 'La duración es obligatoria.',
            'duration.integer' => 'La duración debe ser un número entero.',
            'genre_id.required' => 'El ID del género es obligatorio.',
            'genre_id.exists' => 'El género seleccionado no existe en la base de datos.',
            'album_id.exists' => 'El álbum seleccionado no existe en la base de datos.',
            'es_binaural.required' => 'El campo es_binaural es obligatorio.',
            'es_binaural.boolean' => 'El campo es_binaural debe ser verdadero o falso.',
            'frecuencia.required_if' => 'La frecuencia es obligatoria cuando es_binaural es verdadero.',
            'frecuencia.numeric' => 'La frecuencia debe ser un valor numérico.',
        ]);

        $imageFilePath = $request->hasFile('image_file')
            ? Cloudinary::upload($request->file('image_file')->getRealPath(), ['folder' => 'audios/images'])->getSecurePath()
            : null;

        $audioFilePath = Cloudinary::upload($request->file('audio_file')->getRealPath(), [
            'resource_type' => 'video',
            'folder' => 'audios/mp3'
        ])->getSecurePath();

        $audio = Audio::create(array_merge($request->only([
            'title',
            'description',
            'duration',
            'genre_id',
            'album_id',
            'es_binaural',
            'frecuencia'
        ]), [
            'image_file' => $imageFilePath,
            'audio_file' => $audioFilePath,
        ]));

        return response()->json($audio, 201);
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
            if ($audio->image_file)
                Cloudinary::destroy('images/' . pathinfo($audio->image_file, PATHINFO_FILENAME));
            if ($audio->audio_file)
                Cloudinary::destroy('audios/' . pathinfo($audio->audio_file, PATHINFO_FILENAME), ['resource_type' => 'video']);
            $audio->delete();
            DB::commit();
            return response()->json(['message' => 'Audio eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el audio: ' . $e->getMessage()], 400);
        }
    }
}