<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ApiTrait
{

    //S C O P E S
    public function scopeIncluded(Builder $query)
    {

        if (empty($this->allowIncluded) || empty(request('included'))) {

            return;

        }

        $relations = explode(',', request('included')); //busqueda cliente=[audios,relacion2]

        $allowIncluded = collect($this->allowIncluded);

        foreach ($relations as $key => $value) {
            if (!$allowIncluded->contains($value)) {
                unset($relations[$key]);
            }
        }

        $query->with($relations);
        //http://tranquilidad.test/v1/genres?included=posts

    }


    public function scopeFilter(Builder $query)
    {
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return;
        }

        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);

        foreach ($filters as $filter => $value) {
            if ($allowFilter->contains($filter)) {
                if ($filter === 'category') {
                    $query->where($filter, '=', $value); // Igualdad exacta para category
                } else {
                    $query->where($filter, 'LIKE', '%' . $value . '%'); // LIKE para otros campos
                }
            }
        }
    }
    public function scopeSort(Builder $query)
    {

        if (empty($this->allowSort) || empty(request('sort'))) {
            return;
        }

        $sortFields = explode(',', request('sort'));
        $allowSort = collect($this->allowSort);

        foreach ($sortFields as $sortField) {

            $direction = 'asd';
            if (substr($sortField, 0, 1) == '-') {
                $direction = 'desc';
                $sortField = substr($sortField, 1);


            }

            if ($allowSort->contains($sortField)) {
                $query->orderBy($sortField, $direction);

            }
        }
        //http://tranquilidad.test/v1/genres?sort=name

    }

    public function scopeGetOrPaginate(Builder $query)
    {

        if (request('perPage')) {
            $perPage = intval(request('perPage')); //cadena a numero

            if ($perPage) {
                return $query->paginate($perPage);

            }

        }
        return $query->get();
    }

    //http://tranquilidad.test/v1/genres?perPage=2 
}


