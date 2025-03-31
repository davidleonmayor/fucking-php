<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Album;
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
        Album::truncate(); // Añadimos el truncate para los álbumes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Insertar usuarios
        $this->users();

        // Crear álbumes
        $this->albums();

        // Insertar géneros
        $this->genres();

        // Insertar audios
        $this->audios();

        // Crear playlists y relacionarlas con audios y usuarios
        $this->playlists();
    }

    private function users()
    {
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
    }

    private function genres()
    {
        $genres = [
            [
                'name' => 'Clásica',
                'description' => 'Música Clásica',
                'image_local_path' => storage_path('app/public/genre-clasica.png'),
            ],
            [
                'name' => 'Ambiental',
                'description' => 'Música Ambiental',
                'image_local_path' => storage_path('app/public/genre-ambiental.jpg'),
            ],
            [
                'name' => 'Instrumental',
                'description' => 'Música Instrumental',
                'image_local_path' => storage_path('app/public/genre-instrumental.png'),
            ],
            [
                'name' => 'Electrónica',
                'description' => 'Música Electrónica',
                'image_local_path' => storage_path('app/public/genre-electronica.jpg'),
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
                echo "Error al subir la imagen para " . $genreData['name'] . ": " . $e->getMessage() . "\n";
            }
        }
    }

    private function audios()
    {
        // Obtener los géneros creados para asociar los audios
        $clasicaGenre = Genre::where('name', 'Clásica')->first();
        $ambientalGenre = Genre::where('name', 'Ambiental')->first();
        $instrumentalGenre = Genre::where('name', 'Instrumental')->first();
        $electronicaGenre = Genre::where('name', 'Electrónica')->first();

        // Obtener los álbumes creados para asociar los audios
        $dormirAlbum = Album::where('title', 'Dormir')->first();
        $relajarseAlbum = Album::where('title', 'Relajarse')->first();
        $concentrarseAlbum = Album::where('title', 'Concentrarse')->first();
        $gamerAlbum = Album::where('title', 'Gamer')->first();

        // Verificar que todos los géneros y álbumes existan
        if (!$clasicaGenre || !$ambientalGenre || !$instrumentalGenre || !$electronicaGenre) {
            echo "Error: Géneros no encontrados. Asegúrate de que los géneros se hayan creado primero.\n";
            return;
        }

        if (!$dormirAlbum || !$relajarseAlbum || !$concentrarseAlbum || !$gamerAlbum) {
            echo "Error: Álbumes no encontrados. Asegúrate de que los álbumes se hayan creado primero.\n";
            return;
        }

        $audios = [
            [
                'title' => 'Rock Meditation',
                'description' => 'Una pista de meditación inspirada en el rock',
                'image_local_path' => storage_path('app/public/El-piano.jpg'),
                'audio_local_path' => storage_path('app/public/sample-audio.mp3'),
                'duration' => 600, // 10 minutos en segundos
                'genre_id' => $clasicaGenre->id,
                'album_id' => $dormirAlbum->id, // Asociar con el álbum "Dormir"
                'es_binaural' => true,
                'frecuencia' => 432.0,
            ],
            [
                'title' => 'Jazz Relaxation',
                'description' => 'Una pista de audio relajante de jazz',
                'image_local_path' => storage_path('app/public/El-piano.jpg'),
                'audio_local_path' => storage_path('app/public/sample-audio.mp3'),
                'duration' => 900, // 15 minutos en segundos
                'genre_id' => $ambientalGenre->id,
                'album_id' => $relajarseAlbum->id, // Asociar con el álbum "Relajarse"
                'es_binaural' => false,
                'frecuencia' => null,
            ],
            [
                'title' => 'Piano Dreams',
                'description' => 'Melodías de piano para soñar',
                'image_local_path' => storage_path('app/public/El-piano.jpg'),
                'audio_local_path' => storage_path('app/public/sample-audio.mp3'),
                'duration' => 720, // 12 minutos en segundos
                'genre_id' => $instrumentalGenre->id,
                'album_id' => $concentrarseAlbum->id, // Asociar con el álbum "Concentrarse"
                'es_binaural' => false,
                'frecuencia' => null,
            ],
            [
                'title' => 'Electronic Vibes',
                'description' => 'Vibras electrónicas para energizarte',
                'image_local_path' => storage_path('app/public/El-piano.jpg'),
                'audio_local_path' => storage_path('app/public/sample-audio.mp3'),
                'duration' => 840, // 14 minutos en segundos
                'genre_id' => $electronicaGenre->id,
                'album_id' => $gamerAlbum->id, // Asociar con el álbum "Gamer"
                'es_binaural' => true,
                'frecuencia' => 440.0,
            ],
        ];

        foreach ($audios as $audioData) {
            try {
                // Subir imagen a Cloudinary
                $imageUrl = null;
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
                    'album_id' => $audioData['album_id'], // Asignar el album_id
                    'es_binaural' => $audioData['es_binaural'],
                    'frecuencia' => $audioData['frecuencia'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                echo "Error al subir el audio para " . $audioData['title'] . ": " . $e->getMessage() . "\n";
            }
        }
    }

    private function playlists()
    {
        // Obtener usuarios
        $santiago = User::where('email', 'SantiagoTorres2@gmail.com')->first();
        $maria = User::where('email', 'MariaLopez2@gmail.com')->first();

        // Obtener audios por título para relacionarlos en las playlists
        $rockAudio = Audio::where('title', 'Rock Meditation')->first();
        $jazzAudio = Audio::where('title', 'Jazz Relaxation')->first();
        $pianoAudio = Audio::where('title', 'Piano Dreams')->first();
        $electronicAudio = Audio::where('title', 'Electronic Vibes')->first();

        // Playlist para Santiago: asocia dos audios (por ejemplo, Clásica y Instrumental)
        if ($santiago && $rockAudio && $pianoAudio) {
            $playlistSantiago = Playlist::create([
                'name' => 'Santiago Playlist',
                'user_id' => $santiago->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Asociar ambos audios a la playlist de Santiago
            $playlistSantiago->audios()->attach([$rockAudio->id, $pianoAudio->id]);
        }

        // Playlist para Maria: asocia dos audios (por ejemplo, Ambiental y Electrónica)
        if ($maria && $jazzAudio && $electronicAudio) {
            $playlistMaria = Playlist::create([
                'name' => 'Maria Playlist',
                'user_id' => $maria->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $playlistMaria->audios()->attach([$jazzAudio->id, $electronicAudio->id]);
        }
    }

    private function albums()
    {
        $albums = [
            [
                'title' => 'Dormir',
                'description' => 'Música y sonidos para conciliar el sueño.',
                'image_local_path' => storage_path('app/public/album-dormir.png'),
            ],
            [
                'title' => 'Relajarse',
                'description' => 'Melodías relajantes y sonidos ambientales.',
                'image_local_path' => storage_path('app/public/album-relajarse.png'),
            ],
            [
                'title' => 'Concentrarse',
                'description' => 'Audios y ritmos para mejorar la concentración.',
                'image_local_path' => storage_path('app/public/album-concentrarse.png'),
            ],
            [
                'title' => 'Gamer',
                'description' => 'Música energética e inspirada en videojuegos.',
                'image_local_path' => storage_path('app/public/album-gamer.png'),
            ],
        ];

        foreach ($albums as $albumData) {
            try {
                // Subir imagen a Cloudinary
                $imageUrl = null;
                if (file_exists($albumData['image_local_path'])) {
                    $uploadedImage = Cloudinary::upload($albumData['image_local_path'], [
                        'folder' => 'albums/images',
                        'public_id' => Str::random(10)
                    ]);
                    $imageUrl = $uploadedImage->getSecurePath();
                } else {
                    echo "Archivo de imagen no encontrado: " . $albumData['image_local_path'] . "\n";
                }

                // Crear o actualizar el álbum
                Album::firstOrCreate(
                    ['title' => $albumData['title']],
                    [
                        'description' => $albumData['description'],
                        'image_path' => $imageUrl,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } catch (\Exception $e) {
                echo "Error al subir la imagen para " . $albumData['title'] . ": " . $e->getMessage() . "\n";
            }
        }
    }
}