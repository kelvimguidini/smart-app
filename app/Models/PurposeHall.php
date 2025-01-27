<?php

namespace App\Models;

use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PurposeHall extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;

    protected $fillable = ['name'];
    protected $table = 'purpose_hall';

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

    public function event_hall_opt()
    {
        return $this->belongsTo(EventHallOpt::class);
    }
}
