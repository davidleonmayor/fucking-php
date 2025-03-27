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
    /**
     * Display a listing of the podcasts.
     */
    public function index()
    {
        $podcasts = Podcast::included()
            ->filter()
            ->sort()
            ->getOrPaginate();
        return response()->json($podcasts);
    }

    /**
     * Store a newly created podcast in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_file' => 'required|mimes:mp4,mov,ogg,qt|max:20000|unique:podcasts,video_file',
            'duration' => 'required|integer',
        ]);

        $imageFilePath = null;
        $videoFilePath = null;

        // Handle image file upload if present
        if ($request->hasFile('image_file')) {
            $imageFile = $request->file('image_file');
            if ($imageFile->isValid()) {
                try {
                    $uploadedImage = Cloudinary::upload($imageFile->getRealPath(), [
                        'folder' => 'podcasts/images',
                        'public_id' => Str::random(10)
                    ]);
                    $imageFilePath = $uploadedImage->getSecurePath();
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Failed to upload image to Cloudinary: ' . $e->getMessage()], 400);
                }
            } else {
                return response()->json(['error' => 'Invalid image file'], 400);
            }
        }

        // Handle video file upload
        if ($request->hasFile('video_file')) {
            $videoFile = $request->file('video_file');
            if ($videoFile->isValid()) {
                try {
                    $uploadedVideo = Cloudinary::upload($videoFile->getRealPath(), [
                        'resource_type' => 'video',
                        'folder' => 'podcasts/videos',
                        'public_id' => Str::random(10)
                    ]);
                    $videoFilePath = $uploadedVideo->getSecurePath();
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Failed to upload video to Cloudinary: ' . $e->getMessage()], 400);
                }
            } else {
                return response()->json(['error' => 'Invalid video file'], 400);
            }
        }

        // Create the podcast
        $podcast = Podcast::create([
            'title' => $request->title,
            'description' => $request->description,
            'image_file' => $imageFilePath,
            'video_file' => $videoFilePath,
            'duration' => $request->duration,
        ]);

        return response()->json($podcast, 201);
    }

    /**
     * Display the specified podcast.
     */
    public function show(Podcast $podcast)
    {
        $podcast->load('playlists');
        return response()->json($podcast);
    }

    /**
     * Update the specified podcast in storage.
     */
    public function update(Request $request, Podcast $podcast)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'image_file' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_file' => 'sometimes|nullable|mimes:mp4,mov,ogg,qt|max:20000|unique:podcasts,video_file,' . $podcast->id,
            'duration' => 'sometimes|required|integer',
        ]);

        DB::beginTransaction();

        try {
            // Handle new image file if present
            if ($request->hasFile('image_file')) {
                if ($podcast->image_file) {
                    $publicId = pathinfo(basename($podcast->image_file), PATHINFO_FILENAME);
                    Cloudinary::destroy('podcasts/images/' . $publicId);
                }

                $imageFile = $request->file('image_file');
                if ($imageFile->isValid()) {
                    $uploadedImage = Cloudinary::upload($imageFile->getRealPath(), [
                        'folder' => 'podcasts/images',
                        'public_id' => Str::random(10)
                    ]);
                    $podcast->image_file = $uploadedImage->getSecurePath();
                } else {
                    throw new \Exception('Invalid image file');
                }
            }

            // Handle new video file if present
            if ($request->hasFile('video_file')) {
                if ($podcast->video_file) {
                    $publicId = pathinfo(basename($podcast->video_file), PATHINFO_FILENAME);
                    Cloudinary::destroy('podcasts/videos/' . $publicId, ['resource_type' => 'video']);
                }

                $videoFile = $request->file('video_file');
                if ($videoFile->isValid()) {
                    $uploadedVideo = Cloudinary::upload($videoFile->getRealPath(), [
                        'resource_type' => 'video',
                        'folder' => 'podcasts/videos',
                        'public_id' => Str::random(10)
                    ]);
                    $podcast->video_file = $uploadedVideo->getSecurePath();
                } else {
                    throw new \Exception('Invalid video file');
                }
            }

            // Update other fields
            $podcast->title = $request->input('title', $podcast->title);
            $podcast->description = $request->input('description', $podcast->description);
            $podcast->duration = $request->input('duration', $podcast->duration);

            $podcast->save();
            DB::commit();

            return response()->json($podcast);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update podcast: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified podcast from storage.
     */
    public function destroy(Podcast $podcast)
    {
        DB::beginTransaction();

        try {
            // Delete associated files from Cloudinary
            if ($podcast->image_file) {
                $publicId = pathinfo(basename($podcast->image_file), PATHINFO_FILENAME);
                Cloudinary::destroy('podcasts/images/' . $publicId);
            }

            if ($podcast->video_file) {
                $publicId = pathinfo(basename($podcast->video_file), PATHINFO_FILENAME);
                Cloudinary::destroy('podcasts/videos/' . $publicId, ['resource_type' => 'video']);
            }

            // Delete the podcast
            $podcast->delete();
            DB::commit();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete podcast: ' . $e->getMessage()], 400);
        }
    }
}