<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Contracts\Auth\CanResetPassword;

class User extends Authenticatable implements CanResetPassword

{
    use HasApiTokens, HasFactory, Notifiable, ApiTrait;
    protected $table = 'users';

     //Listas Blancas
     protected $allowIncluded = ['likes', 'histories', 'playlists'];
     protected $allowFilter = ['id', 'name'];
     protected $allowSort = ['id', 'name'];
 
 

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthdate',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];



    /**
     * Relación de uno a muchos con el modelo Playlist.
     */
    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }

    /**
     * Relación de uno a muchos con el modelo Like.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Relación de uno a muchos con el modelo History.
     */
    public function histories()
    {
        return $this->hasMany(History::class);
    }


}
