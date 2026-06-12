<?php

namespace App\Models;

use App\Traits\Activatable;
use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerRequester extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;
    use Activatable;

    protected $fillable = ['name', 'customer_id', 'active'];
    protected $table = 'customer_requesters';

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
