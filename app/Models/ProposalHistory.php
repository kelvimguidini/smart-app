<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalHistory extends Model
{
    protected $fillable = [
        'table_name',
        'record_id',
        'old_data',
        'new_data',
        'action',
        'user_id'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
