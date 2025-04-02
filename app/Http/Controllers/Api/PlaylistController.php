<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    /**
     * Listar todas las playlists del usuario autenticado
     */
    public function index(Request $request)
    {
        try {
            $playlists = Playlist::where('user_id', $request->user()->id)
                ->included() // Relaciones opcionales definidas en el modelo
                ->filter()   // Filtros personalizados
                ->sort()     // Ordenamiento personalizado
                ->getOrPaginate();

            if ($playlists->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron playlists para este usuario.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'message' => 'Playlists obtenidas exitosamente.',
                'data' => $playlists
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener las playlists',
                'message' => 'Ocurrió un error inesperado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva playlist
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'audio_id' => 'required|exists:audios,id', // Validamos el audio inicial
        ]);

        // Verificar que el usuario autenticado sea el propietario
        if ($request->user()->id !== (int) $request->user_id) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No puedes crear una playlist para otro usuario.'
            ], 403);
        }

        $playlist = Playlist::create([
            'name' => $request->name,
            'user_id' => $request->user_id,
        ]);

        $playlist->audios()->attach($request->audio_id);

        return response()->json([
            'message' => 'Playlist creada exitosamente.',
            'data' => $playlist
        ], 201);
    }

    /**
     * Mostrar los detalles de una playlist específica
     */
    public function show(Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No tienes permiso para ver esta playlist.'
            ], 403);
        }

        $playlist->load('audios', 'podcasts');

        return response()->json([
            'message' => 'Playlist obtenida exitosamente.',
            'data' => $playlist
        ], 200);
    }

    /**
     * Actualizar una playlist existente
     */
    public function update(Request $request, Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No puedes modificar esta playlist porque no te pertenece.'
            ], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'audio_id' => 'sometimes|required|exists:audios,id',
        ]);

        $playlist->update($request->only(['name', 'description']));

        if ($request->has('audio_id')) {
            $playlist->audios()->attach($request->audio_id);
        }

        return response()->json([
            'message' => 'Playlist actualizada exitosamente.',
            'data' => $playlist
        ], 200);
    }

    /**
     * Eliminar una playlist
     */
    public function destroy(Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No puedes eliminar esta playlist porque no te pertenece.'
            ], 403);
        }

        $playlist->delete();

        return response()->json(['message' => 'Playlist eliminada exitosamente.'], 204);
    }

    /**
     * Agregar un audio a una playlist
     */
    public function addAudio(Request $request, Playlist $playlist)
    {
        $request->validate([
            'audio_id' => 'required|exists:audios,id',
        ]);

        if ($playlist->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No puedes modificar esta playlist porque no te pertenece.'
            ], 403);
        }

        $playlist->audios()->attach($request->audio_id);

        return response()->json([
            'message' => 'Audio agregado a la playlist exitosamente.',
            'playlist_id' => $playlist->id,
            'audio_id' => $request->audio_id
        ], 201);
    }

    /**
     * Listar los audios de una playlist
     */
    public function listAudios(Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No tienes permiso para ver los audios de esta playlist.'
            ], 403);
        }

        $audios = $playlist->audios()->get();

        if ($audios->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron audios en esta playlist.',
                'playlist_id' => $playlist->id,
                'playlist_name' => $playlist->name,
                'audios' => []
            ], 200);
        }

        return response()->json([
            'message' => 'Audios obtenidos exitosamente.',
            'playlist_id' => $playlist->id,
            'playlist_name' => $playlist->name,
            'audios' => $audios
        ], 200);
    }

    /**
     * Actualizar la información de un audio en una playlist (ej. orden)
     */
    public function updateAudio(Request $request, Playlist $playlist, $audioId)
    {
        if ($playlist->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No puedes modificar esta playlist porque no te pertenece.'
            ], 403);
        }

        $request->validate([
            'order' => 'required|integer',
        ]);

        if (!$playlist->audios()->where('audio_id', $audioId)->exists()) {
            return response()->json([
                'error' => 'Audio no encontrado en la playlist'
            ], 404);
        }

        $playlist->audios()->updateExistingPivot($audioId, ['order' => $request->order]);

        return response()->json([
            'message' => 'Audio actualizado exitosamente.',
            'playlist_id' => $playlist->id,
            'audio_id' => $audioId,
            'order' => $request->order
        ], 200);
    }

    /**
     * Eliminar un audio de una playlist
     */
    public function removeAudio(Playlist $playlist, $audioId)
    {
        if ($playlist->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No puedes modificar esta playlist porque no te pertenece.'
            ], 403);
        }

        $playlist->audios()->detach($audioId);

        return response()->json(['message' => 'Audio eliminado de la playlist'], 200);
    }

    /**
     * Agregar un podcast a una playlist
     */
    public function addPodcast(Request $request, Playlist $playlist)
    {
        $request->validate([
            'podcast_id' => 'required|exists:podcasts,id',
        ]);

        if ($playlist->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No puedes modificar esta playlist porque no te pertenece.'
            ], 403);
        }

        $playlist->podcasts()->attach($request->podcast_id);

        return response()->json([
            'message' => 'Podcast agregado a la playlist exitosamente.',
            'playlist_id' => $playlist->id,
            'podcast_id' => $request->podcast_id
        ], 201);
    }

    /**
     * Listar los podcasts de una playlist
     */
    public function getPodcasts(Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No tienes permiso para ver los podcasts de esta playlist.'
            ], 403);
        }

        $podcasts = $playlist->podcasts()->get();

        if ($podcasts->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron podcasts en esta playlist.',
                'playlist_id' => $playlist->id,
                'playlist_name' => $playlist->name,
                'podcasts' => []
            ], 200);
        }

        return response()->json([
            'message' => 'Podcasts obtenidos exitosamente.',
            'playlist_id' => $playlist->id,
            'playlist_name' => $playlist->name,
            'podcasts' => $podcasts
        ], 200);
    }

    /**
     * Actualizar la información de un podcast en una playlist (ej. orden)
     */
    public function updatePodcast(Request $request, Playlist $playlist, $podcastId)
    {
        if ($playlist->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No puedes modificar esta playlist porque no te pertenece.'
            ], 403);
        }

        $request->validate([
            'order' => 'required|integer',
        ]);

        if (!$playlist->podcasts()->where('podcast_id', $podcastId)->exists()) {
            return response()->json([
                'error' => 'Podcast no encontrado en la playlist'
            ], 404);
        }

        $playlist->podcasts()->updateExistingPivot($podcastId, ['order' => $request->order]);

        return response()->json([
            'message' => 'Podcast actualizado exitosamente.',
            'playlist_id' => $playlist->id,
            'podcast_id' => $podcastId,
            'order' => $request->order
        ], 200);
    }

    /**
     * Eliminar un podcast de una playlist
     */
    public function removePodcast(Playlist $playlist, $podcastId)
    {
        if ($playlist->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'No puedes modificar esta playlist porque no te pertenece.'
            ], 403);
        }

        $playlist->podcasts()->detach($podcastId);

        return response()->json(['message' => 'Podcast eliminado de la playlist'], 200);
    }
}