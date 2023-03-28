<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Currency extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'symbol', 'sigla'];
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
     * @var int
     */
    protected $sigla = 'sigla';


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

    public function event_hotels()
    {
        return $this->belongsTo(EventHotel::class);
    }
}
