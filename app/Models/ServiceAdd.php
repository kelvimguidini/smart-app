<?php

namespace App\Models;

use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ServiceAdd extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;

    protected $fillable = ['name'];
    protected $table = 'service_add';

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

    public function event_add_opt()
    {
        return $this->belongsTo(EventAddOpt::class);
    }
}
