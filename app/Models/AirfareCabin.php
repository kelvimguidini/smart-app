<?php

namespace App\Models;

use App\Traits\Activatable;
use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirfareCabin extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;
    use Activatable;

    protected $fillable = ['name', 'active'];
    protected $table = 'airfare_cabins';

    protected $id = 'id';
    protected $name = 'name';

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = mb_strtoupper($value);
    }
}
