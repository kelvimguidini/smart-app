<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'city',
        'contact',
        'phone',
        'email',
        'national',
        'iss_percent',
        'service_percent',
        'iva_percent',
        'has_hotel',
        'has_ab',
        'has_hall',
        'has_additional'
    ];

    protected $table = 'provider';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';
    protected $name = 'name';
    protected $city = 'city';
    protected $contact = 'contact';
    protected $phone = 'phone';
    protected $email = 'email';
    protected $national = 'national';
    protected $iss_percent = 'iss_percent';
    protected $service_percent = 'service_percent';
    protected $iva_percent = 'iva_percent';
    protected $has_hotel = 'has_hotel';
    protected $has_ab = 'has_ab';
    protected $has_hall = 'has_hall';
    protected $has_additional = 'has_additional';

    public function event_hotels()
    {
        return $this->belongsTo(EventHotel::class);
    }
}
