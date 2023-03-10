<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAddOpt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_add_id',
        'service_id',
        'measure_id',
        'frequency_id',
        'unit',
        'pax',
        'in',
        'out',
        'count',
        'kickback',
        'received_proposal',
        'received_proposal_percent',
    ];

    protected $table = 'event_add_opt';

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
    protected $event_add_id = 'event_add_id';

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
    protected $service = 'service';


    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $measure = 'measure';


    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $frequency = 'frequency';



    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $unit = 'unit';


    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $pax = 'pax';

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


    public function event_add()
    {
        return $this->hasOne(EventAdd::class, 'id', 'event_add_id');
    }

    public function service()
    {
        return $this->hasOne(ServiceAdd::class, 'id', 'service_id');
    }

    public function measure()
    {
        return $this->hasOne(Measure::class, 'id', 'measure_id');
    }

    public function frequency()
    {
        return $this->hasOne(Frequency::class, 'id', 'frequency_id');
    }
}
