<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Event extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'code',
        'requester',
        'customer_id',
        'sector',
        'pax_base',
        'cost_center',
        'date',
        'date_final',
        'crd_id',
        'hotel_operator',
        'air_operator',
        'land_operator'
    ];
    protected $table = 'event';

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
    protected $code = 'code';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $requester = 'requester';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $customer_id = 'customer_id';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $sector = 'sector';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $pax_base = 'pax_base';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $cost_center = 'cost_center';

    /**
     * The primary key associated with the table.
     *
     * @var DateTime
     */
    protected $date = 'date';

    /**
     * The primary key associated with the table.
     *
     * @var DateTime
     */
    protected $date_final = 'date_final';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $crd_id = 'crd_id';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $hotel_operator = 'hotel_operator';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $air_operator = 'air_operator';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $land_operator = 'land_operator';


    public function crd()
    {
        return $this->hasOne(CRD::class, 'id', 'crd_id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    public function hotel_operator()
    {
        return $this->hasOne(User::class, 'id', 'hotel_operator');
    }

    public function air_operator()
    {
        return $this->hasOne(User::class, 'id', 'air_operator');
    }

    public function land_operator()
    {
        return $this->hasOne(User::class, 'id', 'land_operator');
    }

    public function event_hotels()
    {
        return $this->belongsTo(EventHotel::class);
    }
}
