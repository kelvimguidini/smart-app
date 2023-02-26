<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Apto extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];
    protected $table = 'apto';

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
        return $this->belongsToMany(Provider::class, 'apto_hotel', 'hotel_id');
    }
}
