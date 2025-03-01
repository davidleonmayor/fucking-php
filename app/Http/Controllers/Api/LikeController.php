<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $likes = Like::with(['likeable', 'user'])
            ->filter()
            ->sort()
            ->getOrPaginate();
    
        return response()->json($likes);
    }
    
    //para User FICTICIO


    public function store(Request $request)
    {
        // Validar los datos entrantes
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id', // Asegurarse de que el user_id existe en la tabla users
            'likeable_type' => 'required|string', // Verificar que el tipo sea una cadena (puedes mejorar esta validación si solo tienes unos pocos tipos válidos)
            'likeable_id' => 'required|integer', // Asegurar que el ID de likeable sea un número entero
        ]);

        // Verificar si el like ya existe para evitar duplicados
        $existingLike = Like::where('user_id', $validatedData['user_id'])
            ->where('likeable_type', $validatedData['likeable_type'])
            ->where('likeable_id', $validatedData['likeable_id'])
            ->first();

        if ($existingLike) {
            return response()->json(['success' => false, 'message' => 'Like already exists.'], 409);
        }

        // Si no existe, creamos el like
        $like = Like::create($validatedData);
        return response()->json(['success' => true, 'like' => $like]);
    }








    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {

    //     $validated = $request->validate([
    //         'likeable_type' => 'required|string|in:App\\Models\\Audio,App\\Models\\Podcast',
    //         'likeable_id' => 'required|integer',
    //         'user_id' => 'required|integer|exists:users,id'
    //     ]);

    //     // Validación condicional para la existencia del likeable_id
    //     if ($validated['likeable_type'] === 'App\\Models\\Audio') {
    //         $request->validate([
    //             'likeable_id' => 'exists:audios,id'
    //         ]);
    //     } else if ($validated['likeable_type'] === 'App\\Models\\Podcast') {
    //         $request->validate([
    //             'likeable_id' => 'exists:podcasts,id'
    //         ]);
    //     }

    //     // Verificar si ya existe un like para el mismo usuario y contenido
    //     $existingLike = Like::where('likeable_type', $validated['likeable_type'])
    //         ->where('likeable_id', $validated['likeable_id'])
    //         ->where('user_id', $validated['user_id'])
    //         ->first();

    //     if ($existingLike) {
    //         return response()->json(['error' => 'You have already liked this item.'], 409);
    //     }


    //     // Crear el like después de la validación
    //     $like = Like::create($validated);

    //     return response()->json($like, 201);
    // }

    // Mostrar un like específico
    public function show(Like $like)
    {
        return response()->json($like);
    }

    /**
     * Update the specified resource in storage.
     */


    // Actualizar un like existente
    public function update(Request $request, Like $like)
    {
        $validated = $request->validate([
            'likeable_type' => 'required|string|in:App\\Models\\Audio,App\\Models\\Podcast',
            'likeable_id' => 'required|integer',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $like->update($validated);

        return response()->json($like);
    }

    // // Eliminar un like
    // public function destroy(Like $like)
    // {
    //     $like->delete();

    //     return response()->json(null, 204);
    // }




    public function destroy($id)
    {
        try {
            $user_id = 5;  // Usuario ficticio para pruebas
            // Buscar el like por su ID y usuario
            $like = Like::where('id', $id)->where('user_id', $user_id)->first();

            if ($like) {
                $like->delete();
                return response()->json(['success' => true], 200);
            } else {
                return response()->json(['error' => 'Like not found.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
