<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenreControllercopy extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $genres = Genre::included()
            ->filter()
            ->sort()
            ->getOrPaginate();

        return response()->json($genres);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'name' => 'required|string|max:255|unique:genres,name',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Manejar la carga del archivo de imagen
        $imageFilePath = null;
        if ($request->hasFile('image_file')) {
            try {
                // Subir la imagen a Cloudinary
                $uploadedImage = Cloudinary::upload($request->file('image_file')->getRealPath(), [
                    'folder' => 'genres/images',
                    'public_id' => Str::random(10)
                ]);
                $imageFilePath = $uploadedImage->getSecurePath();
                Log::info('Image uploaded to Cloudinary', ['url' => $imageFilePath]);
            } catch (\Exception $e) {
                Log::error('Failed to upload image to Cloudinary', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Failed to upload image to Cloudinary: ' . $e->getMessage()], 400);
            }
        }

        // Crear el nuevo género
        $genre = Genre::create([
            'name' => $request->name,
            'description' => $request->description,
            'image_path' => $imageFilePath,
        ]);

        return response()->json($genre, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $genre = Genre::included()
            ->findOrFail($id);
        return response()->json($genre);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validar la solicitud
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:genres,name,' . $id,
            'description' => 'sometimes|nullable|string',
            'image_file' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Encontrar el género existente
        $genre = Genre::findOrFail($id);

        // Iniciar una transacción
        DB::beginTransaction();

        try {
            // Manejar la carga del nuevo archivo de imagen si está presente
            if ($request->hasFile('image_file')) {
                // Eliminar la imagen anterior de Cloudinary si existe
                if ($genre->image_path) {
                    $publicId = pathinfo(basename($genre->image_path), PATHINFO_FILENAME);
                    Cloudinary::destroy('genres/images/' . $publicId);
                }

                // Subir la nueva imagen a Cloudinary
                $uploadedImage = Cloudinary::upload($request->file('image_file')->getRealPath(), [
                    'folder' => 'genres/images',
                    'public_id' => Str::random(10)
                ]);
                $genre->image_path = $uploadedImage->getSecurePath();
                Log::info('Updated image uploaded to Cloudinary', ['url' => $genre->image_path]);
            }

            // Actualizar los campos del género
            $genre->name = $request->has('name') ? $request->name : $genre->name;
            $genre->description = $request->has('description') ? $request->description : $genre->description;

            // Guardar los cambios en la base de datos
            $genre->save();

            // Confirmar la transacción
            DB::commit();

            return response()->json($genre, 200);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            Log::error('Failed to update genre', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to update genre: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Genre $genre)
    {
        // Iniciar una transacción
        DB::beginTransaction();

        try {
            // Eliminar la imagen en Cloudinary si existe
            if ($genre->image_path) {
                $publicId = pathinfo(basename($genre->image_path), PATHINFO_FILENAME);
                Cloudinary::destroy('genres/images/' . $publicId);
            }

            // Eliminar el registro del género de la base de datos
            $genre->delete();

            // Confirmar la transacción
            DB::commit();

            return response()->json(['message' => 'Genre and associated image successfully deleted.'], 200);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            Log::error('Failed to delete genre', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to delete genre and associated image: ' . $e->getMessage()], 400);
        }
    }
}