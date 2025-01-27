<?php

namespace App\Models;

use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;

    protected $fillable = [
        'name',
        'city_id',
        'contact',
        'phone_reservations',
        'contact_reservations',
        'phone',
        'email',
        'national',
        'iss_percent',
        'service_percent',
        'iva_percent',
        'iof',
        'service_charge',
        'has_hotel',
        'has_ab',
        'has_hall',
    ];

    protected $table = 'provider';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';
    protected $name = 'name';
    protected $city_id = 'city_id';
    protected $contact = 'contact';
    protected $phone = 'phone';
    protected $email = 'email';
    protected $email_reservations = 'email_reservations';
    protected $contact_reservations = 'contact_reservations';
    protected $national = 'national';
    protected $iss_percent = 'iss_percent';
    protected $service_percent = 'service_percent';
    protected $iva_percent = 'iva_percent';
    protected $has_hotel = 'has_hotel';
    protected $has_ab = 'has_ab';
    protected $has_hall = 'has_hall';

    public function event_hotels()
    {
        return $this->belongsTo(EventHotel::class);
    }

    public function city()
    {
        return $this->hasOne(City::class,  'id', 'city_id');
    }
}
