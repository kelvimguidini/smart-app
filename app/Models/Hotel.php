<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'city', 'contact', 'phone', 'email', 'national'];
    protected $table = 'hotel';


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

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $city = 'city';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $contact = 'contact';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $phone = 'phone';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $email = 'email';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $national = 'national';


    public function event()
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }
}
