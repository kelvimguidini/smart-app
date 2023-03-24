<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventHotelOpt extends Model
{
    use SoftDeletes;

    protected $fillable = ['event_hotel_id', 'apto_hotel_id', 'category_hotel_id', 'broker_id', 'regime_id', 'purpose_id', 'in', 'out', 'count', 'kickback', 'received_proposal', 'received_proposal_percent', 'compare_trivago', 'compare_website_htl', 'compare_omnibess'];
    protected $table = 'event_hotel_opt';

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
    protected $event_hotel_id = 'event_hotel_id';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $apto_hotel_id = 'apto_hotel_id';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $category_hotel_id = 'category_hotel_id';
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
    protected $regime_id = 'regime_id';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $purpose_id = 'purpose_id';


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


    public function event_hotel()
    {
        return $this->hasOne(EventHotel::class, 'id', 'event_hotel_id');
    }

    public function apto_hotel()
    {
        return $this->hasOne(Apto::class, 'id', 'apto_hotel_id');
    }

    public function category_hotel()
    {
        return $this->hasOne(Category::class, 'id', 'category_hotel_id');
    }

    public function broker()
    {
        return $this->hasOne(Broker::class, 'id', 'broker_id');
    }

    public function regime()
    {
        return $this->hasOne(Regime::class, 'id', 'regime_id');
    }

    public function purpose()
    {
        return $this->hasOne(Purpose::class, 'id', 'purpose_id');
    }
}
