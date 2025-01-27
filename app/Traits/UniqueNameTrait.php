<?php

namespace App\Traits;

use App\Exceptions\UniqueNameException;

trait UniqueNameTrait
{
    public static function bootUniqueNameTrait()
    {
        static::saving(function ($model) {

            $name = $model->getAttribute('name');
            $id = $model->getAttribute('id');
            $exists = static::where('name', $name)
                ->where('id', '!=', $id) // Ignora o registro atual
                ->exists();

            if ($exists) {
                throw new UniqueNameException();
            }
        });
    }
}
