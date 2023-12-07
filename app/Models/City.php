<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class City extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'states', 'country'];
    protected $table = 'city';

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

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $states = 'states';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $country = 'country';
}
