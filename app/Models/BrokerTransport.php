<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class BrokerTransport extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];
    protected $table = 'broker_transport';

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
