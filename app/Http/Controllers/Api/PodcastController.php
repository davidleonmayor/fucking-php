<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;

class PodcastController extends Controller
{
    public function index()
    {
        $podcasts = Podcast::included()
            ->filter()
            ->sort()
            ->getOrPaginate();
        return response()->json($podcasts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_file' => 'required|mimes:mp4,mov,ogg,qt,webm|max:20000', // Añadir webm
            'duration' => 'required|integer',
            'category' => 'required|in:Afirmaciones,Motivación,Autoestima',
        ]);

        // Resto del código sigue igual...
        $user = $request->user();

        try {
            $imageFilePath = null;
            $videoFilePath = null;

            if ($request->hasFile('image_file')) {
                $uploadedImage = Cloudinary::upload($request->file('image_file')->getRealPath(), [
                    'folder' => 'podcasts/images',
                    'public_id' => Str::random(10),
                ]);
                $imageFilePath = $uploadedImage->getSecurePath();
            }

            $uploadedVideo = Cloudinary::upload($request->file('video_file')->getRealPath(), [
                'resource_type' => 'video',
                'folder' => 'podcasts/videos',
                'public_id' => Str::random(10),
            ]);
            $videoFilePath = $uploadedVideo->getSecurePath();

            $podcast = Podcast::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'image_file' => $imageFilePath,
                'video_file' => $videoFilePath,
                'duration' => $request->duration,
                'category' => $request->category,
            ]);

            return response()->json($podcast, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al subir archivos: ' . $e->getMessage()], 400);
        }
    }


    public function show($id)
    {
        $podcast = Podcast::findOrFail($id);
        return response()->json($podcast);
    }

    public function update(Request $request, $id)
    {
        // Validar la solicitud
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'image_file' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_file' => 'sometimes|nullable|mimes:mp4,mov,ogg,qt|max:20000',
            'duration' => 'sometimes|required|integer',
            'category' => 'sometimes|required|in:Afirmaciones,Motivación,Autoestima', // Validar el enum
        ]);

        $podcast = Podcast::findOrFail($id);

        DB::beginTransaction();
        try {
            // Actualizar imagen si se proporciona
            if ($request->hasFile('image_file')) {
                if ($podcast->image_file) {
                    $publicId = pathinfo(basename($podcast->image_file), PATHINFO_FILENAME);
                    Cloudinary::destroy('podcasts/images/' . $publicId);
                }
                $uploadedImage = Cloudinary::upload($request->file('image_file')->getRealPath(), [
                    'folder' => 'podcasts/images',
                    'public_id' => Str::random(10),
                ]);
                $podcast->image_file = $uploadedImage->getSecurePath();
            }

            // Actualizar video si se proporciona
            if ($request->hasFile('video_file')) {
                if ($podcast->video_file) {
                    $publicId = pathinfo(basename($podcast->video_file), PATHINFO_FILENAME);
                    Cloudinary::destroy('podcasts/videos/' . $publicId, ['resource_type' => 'video']);
                }
                $uploadedVideo = Cloudinary::upload($request->file('video_file')->getRealPath(), [
                    'resource_type' => 'video',
                    'folder' => 'podcasts/videos',
                    'public_id' => Str::random(10),
                ]);
                $podcast->video_file = $uploadedVideo->getSecurePath();
            }

            // Actualizar otros campos
            $podcast->title = $request->input('title', $podcast->title);
            $podcast->description = $request->input('description', $podcast->description);
            $podcast->duration = $request->input('duration', $podcast->duration);
            $podcast->category = $request->input('category', $podcast->category); // Actualizar categoría
            $podcast->save();

            DB::commit();
            return response()->json($podcast);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar el podcast: ' . $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        $podcast = Podcast::findOrFail($id);

        DB::beginTransaction();
        try {
            // Eliminar imagen de Cloudinary si existe
            if ($podcast->image_file) {
                $publicId = pathinfo(basename($podcast->image_file), PATHINFO_FILENAME);
                Cloudinary::destroy('podcasts/images/' . $publicId);
            }

            // Eliminar video de Cloudinary si existe
            if ($podcast->video_file) {
                $publicId = pathinfo(basename($podcast->video_file), PATHINFO_FILENAME);
                Cloudinary::destroy('podcasts/videos/' . $publicId, ['resource_type' => 'video']);
            }

            // Eliminar el podcast
            $podcast->delete();
            DB::commit();
            return response()->json(['message' => 'Podcast eliminado correctamente'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el podcast: ' . $e->getMessage()], 400);
        }
    }
}
// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\Podcast;
// use Illuminate\Http\Request;
// use Illuminate\Support\Str;
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
// use Illuminate\Support\Facades\DB;


// class PodcastController extends Controller
// {

//     public function index()
//     {
//         $podcast = Podcast::included()
//             ->filter()
//             ->sort()
//             ->getOrPaginate();
//         return response()->json($podcast);
//     }


//     public function store(Request $request)
//     {
//         // Validar la solicitud
//         $request->validate([
//             'title' => 'required|string|max:255',
//             'description' => 'nullable|string',
//             'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
//             'video_file' => 'required|mimes:mp4,mov,ogg,qt|max:20000|unique:podcasts,video_file',
//             'duration' => 'required|integer',
//         ]);

//         $imageFilePath = null;
//         $videoFilePath = null;

//         // Manejar la carga del archivo de imagen si está presente
//         if ($request->hasFile('image_file')) {
//             $imageFile = $request->file('image_file');

//             if ($imageFile->isValid()) {
//                 try {
//                     // Subir la imagen a la carpeta "podcasts/images"
//                     $uploadedImage = Cloudinary::upload($imageFile->getRealPath(), [
//                         'folder' => 'podcasts/images',
//                         'public_id' => Str::random(10)
//                     ]);
//                     $imageFilePath = $uploadedImage->getSecurePath(); // Obtener la URL segura de la imagen
//                 } catch (\Exception $e) {
//                     return response()->json(['error' => 'Failed to upload image to Cloudinary: ' . $e->getMessage()], 400);
//                 }
//             } else {
//                 return response()->json(['error' => 'Invalid image file or file not valid'], 400);
//             }
//         }

//         // Manejar la carga del archivo de video
//         if ($request->hasFile('video_file')) {
//             $videoFile = $request->file('video_file');

//             if ($videoFile->isValid()) {
//                 try {
//                     // Subir el video a la carpeta "podcasts/videos"
//                     $uploadedVideo = Cloudinary::upload($videoFile->getRealPath(), [
//                         'resource_type' => 'video', // Especifica que el tipo de recurso es un video
//                         'folder' => 'podcasts/videos',
//                         'public_id' => Str::random(10)
//                     ]);
//                     $videoFilePath = $uploadedVideo->getSecurePath();
//                 } catch (\Exception $e) {
//                     return response()->json(['error' => 'Failed to upload video to Cloudinary: ' . $e->getMessage()], 400);
//                 }
//             } else {
//                 return response()->json(['error' => 'Invalid video file or file not valid'], 400);
//             }
//         }

//         // Crear el nuevo registro de podcast
//         $podcast = Podcast::create([
//             'title' => $request->title,
//             'description' => $request->description,
//             'image_file' => $imageFilePath,
//             'video_file' => $videoFilePath,
//             'duration' => $request->duration,
//         ]);

//         return response()->json($podcast, 201);

//     }

//     public function show($id)
//     {
//         $podcast = Podcast::findOrFail($id);
//         return response()->json($podcast, 200);
//     }




//     public function update(Request $request, $id)
//     {
//         // Validar la solicitud
//         $request->validate([
//             'title' => 'sometimes|required|string|max:255',
//             'description' => 'sometimes|nullable|string',
//             'image_file' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
//             'video_file' => 'sometimes|nullable|mimes:mp4,mov,ogg,qt|max:20000|unique:podcasts,video_file,' . $id,
//             'duration' => 'sometimes|required|integer',
//         ]);

//         // Encontrar el podcast existente
//         $podcast = Podcast::findOrFail($id);

//         DB::beginTransaction();

//         try {
//             // Manejar la carga del nuevo archivo de imagen si está presente
//             if ($request->hasFile('image_file')) {
//                 if ($podcast->image_file) {
//                     $publicId = pathinfo(basename($podcast->image_file), PATHINFO_FILENAME);
//                     Cloudinary::destroy('podcasts/images/' . $publicId);
//                 }

//                 $imageFile = $request->file('image_file');
//                 if ($imageFile->isValid()) {
//                     $uploadedImage = Cloudinary::upload($imageFile->getRealPath(), [
//                         'folder' => 'podcasts/images',
//                         'public_id' => Str::random(10)
//                     ]);
//                     $podcast->image_file = $uploadedImage->getSecurePath();
//                 } else {
//                     throw new \Exception('Invalid image file');
//                 }
//             }

//             // Manejar la carga del nuevo archivo de video si está presente
//             if ($request->hasFile('video_file')) {
//                 if ($podcast->video_file) {
//                     $publicId = pathinfo(basename($podcast->video_file), PATHINFO_FILENAME);
//                     Cloudinary::destroy('podcasts/videos/' . $publicId, ['resource_type' => 'video']);
//                 }

//                 $videoFile = $request->file('video_file');
//                 if ($videoFile->isValid()) {
//                     $uploadedVideo = Cloudinary::upload($videoFile->getRealPath(), [
//                         'resource_type' => 'video',
//                         'folder' => 'podcasts/videos',
//                         'public_id' => Str::random(10)
//                     ]);
//                     $podcast->video_file = $uploadedVideo->getSecurePath();
//                 } else {
//                     throw new \Exception('Invalid video file');
//                 }
//             }

//             // Actualizar los demás campos
//             $podcast->title = $request->has('title') ? $request->title : $podcast->title;
//             $podcast->description = $request->has('description') ? $request->description : $podcast->description;
//             $podcast->duration = $request->has('duration') ? $request->duration : $podcast->duration;

//             $podcast->save();
//             DB::commit();

//             return response()->json($podcast, 200);

//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json(['error' => 'Failed to update podcast: ' . $e->getMessage()], 400);
//         }

//     }

//     /**
//      * Remove the specified resource from storage.
//      */
//     public function destroy($id)
//     {

//         DB::beginTransaction();

//         try {
//             // Encontrar el podcast
//             $podcast = Podcast::findOrFail($id);

//             // Eliminar la imagen en Cloudinary si existe
//             if ($podcast->image_file) {
//                 $publicId = pathinfo(basename($podcast->image_file), PATHINFO_FILENAME);
//                 Cloudinary::destroy('podcasts/images/' . $publicId);
//             }

//             // Eliminar el video en Cloudinary si existe
//             if ($podcast->video_file) {
//                 $publicId = pathinfo(basename($podcast->video_file), PATHINFO_FILENAME);
//                 Cloudinary::destroy('podcasts/videos/' . $publicId, ['resource_type' => 'video']);
//             }

//             // Eliminar el registro de la base de datos
//             $podcast->delete();

//             DB::commit();
//             return response()->json(['message' => 'Podcast and associated files successfully deleted.'], 200);

//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json(['error' => 'Failed to delete podcast: ' . $e->getMessage()], 400);
//         }

//     }
// }
