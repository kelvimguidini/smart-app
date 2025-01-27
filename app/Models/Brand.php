<?php

namespace App\Models;

use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Brand extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;

    protected $fillable = ['name'];
    protected $table = 'car_brand';

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

    public function event_transports_opt()
    {
        return $this->belongsTo(EventTransportOpt::class);
    }
}
