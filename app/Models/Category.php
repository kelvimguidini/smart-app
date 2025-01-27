<?php

namespace App\Models;

use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Category extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;

    protected $fillable = ['name'];
    protected $table = 'category';

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


    public function hotels()
    {
        return $this->belongsToMany(Provider::class, 'category_hotel', 'hotel_id');
    }
}
