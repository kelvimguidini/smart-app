<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Local extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];
    protected $table = 'local';

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
        return $this->belongsTo(EventABOptOpt::class);
    }
}
