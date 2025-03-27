<?php

namespace App\Models;

use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    use HasFactory, ApiTrait;

    protected $guarded = [];

    // Listas Blancas
    protected $allowIncluded = ['tags', 'likes', 'histories', 'playlists'];
    protected $allowFilter = ['id', 'title', 'description', 'duration'];
    protected $allowSort = ['id', 'title', 'duration'];

    // Relación polimórfica muchos a muchos con el modelo Tag
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    // Relación polimórfica inversa con el modelo Like
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    // Relación polimórfica con el modelo History
    public function histories()
    {
        return $this->morphMany(History::class, 'historable');
    }

    // Relación muchos a muchos con Playlist
    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlist_podcast');
    }
}