<?php

namespace App\Models;

use App\Traits\Activatable;
use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CRD extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;
    use Activatable;

    protected $fillable = ['name', 'number', 'customer_id', 'active'];
    protected $table = 'crd';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';


    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $number = 'number';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $customer_id = 'customer_id';

    public function events()
    {
        return $this->belongsTo(Event::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class,  'id', 'customer_id');
    }
}
