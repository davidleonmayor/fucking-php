<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Genre;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Agregamos DB para manejar claves foráneas
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Desactivar las restricciones de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Elimina todos los registros para evitar duplicados
        User::truncate();
        Genre::truncate();

        // Volver a activar las restricciones de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Crear usuarios
        $users = [
            [
                'name' => 'Santiago Torres',
                'email' => 'SantiagoTorres2@gmail.com',
                'password' => Hash::make('password123'),
                'birthdate' => '1990-01-01',
                'email_verified_at' => now(), // Agregamos email_verified_at para cumplir con el esquema
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

        // Insertar los géneros musicales
        $this->genres();
    }

    private function genres()
    {
        $genres = [
            [
                'name' => 'Rock',
                'description' => 'Música rock',
                'image_local_path' => storage_path('app/public/genre-just-relax.jpg'),
            ],
            [
                'name' => 'Jazz',
                'description' => 'Música jazz',
                'image_local_path' => storage_path('app/public/genre-just-relax.jpg'),
            ]
        ];

        foreach ($genres as $genreData) {
            try {
                // Subir imagen a Cloudinary si el archivo existe
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

                // Crear el género en la base de datos
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
}