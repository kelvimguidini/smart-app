<?php

namespace App\Models;

use App\Traits\Activatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerMetadata extends Model
{
    use SoftDeletes;
    use Activatable;

    protected $fillable = ['customer_id', 'type', 'value', 'active'];
    protected $table = 'customer_metadata';

    /**
     * Relationship with Customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
