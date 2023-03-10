<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventHallOpt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_hall_id',
        'service_id',
        'purpose_id',
        'broker_id',
        'in',
        'out',
        'count',
        'kickback',
        'received_proposal',
        'received_proposal_percent',
    ];

    protected $table = 'event_hall_opt';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $event_hall_id = 'event_hall_id';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $service_id = 'service_id';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $purpose_id = 'purpose_id';
    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $broker_id = 'broker_id';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $in = 'in';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $out = 'out';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $count = 'count';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $kickback = 'kickback';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $received_proposal = 'received_proposal';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $received_proposal_percent = 'received_proposal_percent';


    public function event_hall()
    {
        return $this->hasOne(EventHall::class, 'id', 'event_hall_id');
    }

    public function service()
    {
        return $this->hasOne(ServiceHall::class, 'id', 'service_id');
    }

    public function purpose()
    {
        return $this->hasOne(PurposeHall::class, 'id', 'purpose_id');
    }

    public function broker()
    {
        return $this->hasOne(Broker::class, 'id', 'broker_id');
    }
}
