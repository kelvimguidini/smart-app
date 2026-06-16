<?php

namespace App\Models;

use App\Traits\Activatable;
use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderAirfare extends Model
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
        'iva_percent',
        'active',
        'codestur',
        'payment_method'
    ];

    protected $table = 'provider_airfare';

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
    protected $payment_method = 'payment_method';

    public function city()
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }
}
