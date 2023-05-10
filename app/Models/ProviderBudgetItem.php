<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ProviderBudgetItem extends Model
{
    use SoftDeletes;

    protected $table = 'provider_budget_item';

    protected $fillable = [
        'provider_budget_id',
        'event_hotel_opt_id',
        'event_ab_opt_id',
        'event_add_opt_id',
        'event_hall_opt_id',
        'comission',
        'value',
        'comment',
    ];

    public function providerBudget()
    {
        return $this->belongsTo(ProviderBudget::class);
    }

    public function eventHotelOption()
    {
        return $this->belongsTo(EventHotelOption::class, 'event_hotel_opt_id');
    }

    public function eventAbOption()
    {
        return $this->belongsTo(EventAbOption::class, 'event_ab_opt_id');
    }

    public function eventAddOption()
    {
        return $this->belongsTo(EventAddOption::class, 'event_add_opt_id');
    }

    public function eventHallOption()
    {
        return $this->belongsTo(EventHallOption::class, 'event_hall_opt_id');
    }
}
