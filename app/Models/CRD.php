<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CRD extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'cnpj'];
    protected $table = 'crd';

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
    protected $cnpj = 'cnpj';

    public function events()
    {
        return $this->belongsTo(Event::class);
    }
}
