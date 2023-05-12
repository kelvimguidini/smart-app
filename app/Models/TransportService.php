<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class TransportService extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];
    protected $table = 'transport_service';

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

    public function event_transports_opt()
    {
        return $this->belongsTo(EventTransportOpt::class);
    }
}
