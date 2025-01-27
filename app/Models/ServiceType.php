<?php

namespace App\Models;

use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ServiceType extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;

    protected $fillable = ['name'];
    protected $table = 'service_type';

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

    public function event_abs_opt()
    {
        return $this->belongsTo(EventABOpt::class);
    }
}
