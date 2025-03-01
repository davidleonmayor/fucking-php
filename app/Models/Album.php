<?php

namespace App\Models;

use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory, ApiTrait;

    protected $guarded = [];

    // Listas blancas para inclusión, filtrado y ordenamiento en la API
    protected $allowIncluded = ['audios', 'genre'];
    protected $allowFilter = ['id', 'title', 'description', 'genre_id'];
    protected $allowSort = ['id', 'title'];

    // Relación Uno a Muchos con Audio
    public function audios()
    {
        return $this->hasMany(Audio::class);
    }

    // Relación Uno a Muchos Inversa con Genre
    public function genre()
    {
        return $this->belongsTo(Genre::class)->withDefault();
    }

    // ✅ Query Scope para incluir relaciones dinámicamente
    public function scopeIncluded($query)
    {
        if ($relations = request('included')) {
            $allowedRelations = array_intersect(explode(',', $relations), $this->allowIncluded); // Filtrar relaciones permitidas
            $query->with($allowedRelations);
        }
    }
}
