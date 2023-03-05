<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventABOpt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_ab_id',
        'service_id',
        'service_type_id',
        'broker_id',
        'local_id',
        'in',
        'out',
        'count',
        'kickback',
        'received_proposal',
        'received_proposal_percent',
        'compare_trivago',
        'compare_website_htl',
        'compare_omnibess'
    ];

    protected $table = 'event_ab_opt';

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
    protected $event_ab_id = 'event_ab_id';

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
    protected $service_type_id = 'service_type_id';
    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $broker_id = 'broker_id';
    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $local_id = 'local_id';

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


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $compare_trivago = 'compare_trivago';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $compare_website_htl = 'compare_website_htl';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $compare_omnibess = 'compare_omnibess';


    public function event_ab()
    {
        return $this->hasOne(EventAB::class, 'id', 'event_ab_id');
    }

    public function service()
    {
        return $this->hasOne(Service::class, 'id', 'service_id');
    }

    public function service_type()
    {
        return $this->hasOne(ServiceType::class, 'id', 'service_type_id');
    }

    public function broker()
    {
        return $this->hasOne(Broker::class, 'id', 'broker_id');
    }

    public function Local()
    {
        return $this->hasOne(Local::class, 'id', 'local_id');
    }
}
