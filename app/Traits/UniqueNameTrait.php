<?php

namespace App\Traits;

use App\Exceptions\UniqueNameException;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait UniqueNameTrait
{
    public static function bootUniqueNameTrait()
    {
        static::saving(function (\Illuminate\Database\Eloquent\Model $model) {

            $name = $model->getAttribute('name');
            $id   = $model->getAttribute('id');

            $query = $model->newQuery()->withoutGlobalScopes()
                ->where('name', $name)
                ->where('id', '!=', $id);

            if (method_exists($model, 'uniqueNameColumns')) {
                foreach ($model->uniqueNameColumns() as $column) {
                    $val = $model->getAttribute($column);
                    if (is_null($val)) {
                        $query->whereNull($column);
                    } else {
                        $query->where($column, $val);
                    }
                }
            }

            // Se o model utilizar SoftDeletes, desconsidera registros excluídos logicamente do teste de unicidade
            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))) {
                $query->whereNull($model->getDeletedAtColumn());
            }

            $exists = $query->exists();

            if ($exists) {
                throw new UniqueNameException();
            }
        });
    }
}
