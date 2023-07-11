<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class EventLocal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'pais',
        'cidade',
        'event_id'
    ];
    protected $table = 'event_local';

    public function event()
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }
}
