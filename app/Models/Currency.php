<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Currency extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'symbol'];
    protected $table = 'currency';

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
    protected $symbol = 'symbol';
}
