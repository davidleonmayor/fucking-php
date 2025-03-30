<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Genre;
use App\Models\Audio;
use App\Models\Playlist;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Desactivar restricciones de claves foráneas para evitar errores al truncar
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Genre::truncate();
        Audio::truncate();
        Playlist::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Crear usuarios
        $users = [
            [
                'name' => 'Santiago Torres',
                'email' => 'SantiagoTorres2@gmail.com',
                'password' => Hash::make('password123'),
                'birthdate' => '1990-01-01',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maria López',
                'email' => 'MariaLopez2@gmail.com',
                'password' => Hash::make('password123'),
                'birthdate' => '1992-05-15',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(['email' => $userData['email']], $userData);
        }

        // Insertar géneros
        $this->genres();

        // Insertar audios
        $this->audios();

        // Crear playlists y relacionarlas con audios y usuarios
        $this->playlists();
    }

    private function genres()
    {
        $genres = [
            [
                'name' => 'Clásica',
                'description' => 'Música Clásica',
                'image_local_path' => storage_path('app/public/genre-just-relax.jpg'),
            ],
            [
                'name' => 'Ambiental',
                'description' => 'Música Ambiental',
                'image_local_path' => storage_path('app/public/genre-just-relax.jpg'),
            ],
            [
                'name' => 'Instrumental',
                'description' => 'Música Instrumental',
                'image_local_path' => storage_path('app/public/genre-just-relax.jpg'),
            ],
            [
                'name' => 'Electrónica',
                'description' => 'Música Electrónica',
                'image_local_path' => storage_path('app/public/genre-just-relax.jpg'),
            ]
        ];

        foreach ($genres as $genreData) {
            try {
                // Subir imagen a Cloudinary si existe
                $imageUrl = null;
                if (file_exists($genreData['image_local_path'])) {
                    $uploadedImage = Cloudinary::upload($genreData['image_local_path'], [
                        'folder' => 'genres/images',
                        'public_id' => Str::random(10)
                    ]);
                    $imageUrl = $uploadedImage->getSecurePath();
                } else {
                    echo "Archivo no encontrado: " . $genreData['image_local_path'] . "\n";
                }

                Genre::firstOrCreate(
                    ['name' => $genreData['name']],
                    [
                        'description' => $genreData['description'],
                        'image_path' => $imageUrl,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } catch (\Exception $e) {
                echo "Error uploading image for " . $genreData['name'] . ": " . $e->getMessage() . "\n";
            }
        }
    }

    private function audios()
    {
        // Obtener los géneros creados para asociar los audios
        $ClasicaGenre = Genre::where('name', 'Clásica')->first();
        $AmbientalGenre = Genre::where('name', 'Ambiental')->first();
        $InstrumentalGenre = Genre::where('name', 'Instrumental')->first();
        $ElectronicaGenre = Genre::where('name', 'Electrónica')->first();

        // Verificar que todos los géneros existan
        if (!$ClasicaGenre || !$AmbientalGenre || !$InstrumentalGenre || !$ElectronicaGenre) {
            echo "Error: Géneros no encontrados. Asegúrate de que los géneros se hayan creado primero.\n";
            return;
        }

        $audios = [
            [
                'title' => 'Rock Meditation',
                'description' => 'A rock-inspired meditation track',
                'image_local_path' => storage_path('app/public/audio-image.jpg'),
                'audio_local_path' => storage_path('app/public/sample-audio.mp3'),
                'duration' => 600, // 10 minutos en segundos
                'genre_id' => $ClasicaGenre->id,
                'album_id' => null,
                'es_binaural' => true,
                'frecuencia' => 432.0,
            ],
            [
                'title' => 'Jazz Relaxation',
                'description' => 'A relaxing jazz audio track',
                'image_local_path' => storage_path('app/public/audio-image.jpg'),
                'audio_local_path' => storage_path('app/public/sample-audio.mp3'),
                'duration' => 900, // 15 minutos en segundos
                'genre_id' => $AmbientalGenre->id,
                'album_id' => null,
                'es_binaural' => false,
                'frecuencia' => null,
            ],
        ];

        foreach ($audios as $audioData) {
            try {
                // Subir imagen a Cloudinary
                $imageUrl = null; // Corrección aquí
                if (file_exists($audioData['image_local_path'])) {
                    $uploadedImage = Cloudinary::upload($audioData['image_local_path'], [
                        'folder' => 'audios/images',
                        'public_id' => Str::random(10)
                    ]);
                    $imageUrl = $uploadedImage->getSecurePath();
                } else {
                    echo "Archivo de imagen no encontrado: " . $audioData['image_local_path'] . "\n";
                }

                // Subir audio a Cloudinary
                $audioUrl = null;
                if (file_exists($audioData['audio_local_path'])) {
                    $uploadedAudio = Cloudinary::upload($audioData['audio_local_path'], [
                        'resource_type' => 'video',
                        'folder' => 'audios/mp3',
                        'public_id' => Str::random(10)
                    ]);
                    $audioUrl = $uploadedAudio->getSecurePath();
                } else {
                    echo "Archivo de audio no encontrado: " . $audioData['audio_local_path'] . "\n";
                }

                Audio::create([
                    'title' => $audioData['title'],
                    'description' => $audioData['description'],
                    'image_file' => $imageUrl,
                    'audio_file' => $audioUrl,
                    'duration' => $audioData['duration'],
                    'genre_id' => $audioData['genre_id'],
                    'album_id' => $audioData['album_id'],
                    'es_binaural' => $audioData['es_binaural'],
                    'frecuencia' => $audioData['frecuencia'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                echo "Error uploading audio for " . $audioData['title'] . ": " . $e->getMessage() . "\n";
            }
        }
    }

    private function playlists()
    {
        // Obtener usuarios
        $santiago = User::where('email', 'SantiagoTorres2@gmail.com')->first();
        $maria = User::where('email', 'MariaLopez2@gmail.com')->first();

        // Obtener audios según género
        $ambientalAudio = Audio::whereHas('genre', function ($query) {
            $query->where('name', 'Ambiental');
        })->first();

        $instrumentalAudio = Audio::whereHas('genre', function ($query) {
            $query->where('name', 'Instrumental');
        })->first();

        if ($santiago && $ambientalAudio && $instrumentalAudio) {
            $playlistSantiago = Playlist::create([
                'name' => 'Santiago Playlist',
                'user_id' => $santiago->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Relacionar ambos audios a la playlist de Santiago
            $playlistSantiago->audios()->attach($ambientalAudio->id);
            $playlistSantiago->audios()->attach($instrumentalAudio->id);
        }

        if ($maria && $instrumentalAudio) {
            $playlistMaria = Playlist::create([
                'name' => 'Maria Playlist',
                'user_id' => $maria->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $playlistMaria->audios()->attach($instrumentalAudio->id);
        }
    }
}