<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Purpose extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];
    protected $table = 'purpose';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $name = 'name';


    public function hotels()
    {
        return $this->belongsToMany(Hotel::class, 'apto_hotel');
    }
}
