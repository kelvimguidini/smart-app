<?php

namespace App\Models;

use App\Traits\Activatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirfareAirport extends Model
{
    use SoftDeletes;
    use Activatable;

    protected $table = 'airfare_airports';

    protected $fillable = [
        'iata_code',
        'name',
        'city',
        'state',
        'country',
        'active'
    ];

    public function setIataCodeAttribute($value)
    {
        $this->attributes['iata_code'] = mb_strtoupper(trim($value));
    }

    /**
     * Scope a query to search airports by term (IATA, name or city).
     */
    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function ($q) use ($term) {
            $q->where('iata_code', 'like', $term . '%')
              ->orWhere('name', 'like', '%' . $term . '%')
              ->orWhere('city', 'like', '%' . $term . '%');
        });
    }
}
