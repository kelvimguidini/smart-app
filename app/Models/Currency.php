<?php

namespace App\Models;

use App\Traits\Activatable;
use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Currency extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;
    use Activatable;

    protected $fillable = ['name', 'symbol', 'sigla', 'active'];
    protected $table = 'currency';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';


    /**
     * Define o valor do atributo 'sigla' para maiúsculas antes de salvar.
     *
     * @param string $value
     * @return void
     */
    public function setSiglaAttribute($value)
    {
        $this->attributes['sigla'] = strtoupper($value);
    }

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
    protected $symbol = 'symbol';

    public function event_hotels()
    {
        return $this->belongsTo(EventHotel::class);
    }
}
