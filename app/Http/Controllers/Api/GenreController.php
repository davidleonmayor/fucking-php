<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Genre;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;


class GenreController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // Listar todos los géneros
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    // Crear un nuevo género
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
        $imageFile = $request->file('image_file');

        if ($imageFile->isValid()) {
            try {
                // Subir la imagen a Cloudinary
                $uploadedImage = Cloudinary::upload($imageFile->getRealPath(), [
                    'folder' => 'genres/images',
                    'public_id' => Str::random(10)
                ]);
                $imageFilePath = $uploadedImage->getSecurePath(); // Obtener la URL segura de la imagen
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to upload image to Cloudinary: ' . $e->getMessage()], 400);
            }
        } else {
            return response()->json(['error' => 'Invalid image file or file not valid'], 400);
        }
    }

    // Crear el nuevo género
    $genre = Genre::create([
        'name' => $request->name,
        'description' => $request->description,
        'image_path' => $imageFilePath, // Almacenar la URL de la imagen
    ]);

    return response()->json($genre, 201);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Genre  $genre
     * @return \Illuminate\Http\Response
     */

    // Mostrar un género específico
    public function show($id)
    {
        $genre = Genre::included()
            ->findOrFail($id);
        return $genre;
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Genre  $genre
     * @return \Illuminate\Http\Response
     */

    // Actualizar un género existente
    public function update(Request $request, $id)
    {
         // Validar la solicitud
    $request->validate([
        'name' => 'sometimes|required|string|max:255|unique:genres,name,',
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
            $imageFile = $request->file('image_file');
            if ($imageFile->isValid()) {
                $uploadedImage = Cloudinary::upload($imageFile->getRealPath(), [
                    'folder' => 'genres/images',
                    'public_id' => Str::random(10)
                ]);
                $genre->image_path = $uploadedImage->getSecurePath(); // Actualizar la URL de la nueva imagen
            } else {
                throw new \Exception('Invalid image file');
            }
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

        return response()->json(['error' => 'Failed to update genre: ' . $e->getMessage()], 400);
    }
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Genre  $genre
     * @return \Illuminate\Http\Response
     */

    // Eliminar un género
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
    
            return response()->json(['error' => 'Failed to delete genre and associated image: ' . $e->getMessage()], 400);
        }
    }
}    