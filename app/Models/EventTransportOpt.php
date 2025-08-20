<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventTransportOpt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_transport_id',
        'broker_id',
        'vehicle_id',
        'model_id',
        'service_id',
        'brand_id',
        'observation',
        'in',
        'out',
        'count',
        'kickback',
        'received_proposal',
        'received_proposal_percent',
        'order',
    ];
    protected $table = 'event_transport_opt';

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
    protected $event_transport_id = 'event_transport_id';

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


    public function event_transport()
    {
        return $this->hasOne(EventTransport::class, 'id', 'event_transport_id');
    }

    public function broker()
    {
        return $this->hasOne(BrokerTransport::class, 'id', 'broker_id');
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'id', 'vehicle_id');
    }

    public function model()
    {
        return $this->hasOne(CarModel::class, 'id', 'model_id');
    }

    public function service()
    {
        return $this->hasOne(TransportService::class, 'id', 'service_id');
    }

    public function brand()
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }

    public function providerBudget()
    {
        return $this->hasMany(ProviderBudget::class, 'event_transport_id', 'id');
    }
}
