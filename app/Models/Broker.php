<?php

namespace App\Models;

use App\Traits\Activatable;
use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Broker extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;
    use Activatable;

    protected $fillable = [
        'name',
        'city_id',
        'contact',
        'phone',
        'email',
        'national',
        'active'
    ];
    protected $table = 'broker';

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

    public function event_hotels_opt()
    {
        return $this->belongsTo(EventHotelOpt::class);
    }

    public function event_abs_opt()
    {
        return $this->belongsTo(EventABOpt::class);
    }

    public function city()
    {
        return $this->hasOne(City::class,  'id', 'city_id');
    }
}
