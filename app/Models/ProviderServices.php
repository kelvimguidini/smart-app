<?php

namespace App\Models;

use App\Traits\Activatable;
use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderServices extends Model
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
        'iss_percent',
        'service_percent',
        'iof',
        'service_charge',
        'iva_percent',
        'has_additional',
        'active',
        'codestur'
    ];

    protected $table = 'provider_services';

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
    protected $national = 'national';
    protected $iss_percent = 'iss_percent';
    protected $service_percent = 'service_percent';
    protected $iva_percent = 'iva_percent';
    protected $codestur = 'codestur';

    public function event_hotels()
    {
        return $this->belongsTo(EventHotel::class);
    }

    public function city()
    {
        return $this->hasOne(City::class,  'id', 'city_id');
    }
}
