<?php

namespace App\Traits;

use App\Exceptions\UniqueNameException;

trait UniqueNameTrait
{
    public static function bootUniqueNameTrait()
    {
        static::saving(function ($model) {

            $name = $model->getAttribute('name');
            $id   = $model->getAttribute('id');

            // withoutGlobalScopes() evita que escopos como SoftDeletes ou 'active'
            // sejam aplicados, prevenindo erros de coluna inexistente (ex: deleted_at).
            $exists = static::withoutGlobalScopes()
                ->where('name', $name)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                throw new UniqueNameException();
            }
        });
    }
}
