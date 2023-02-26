<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CategoryHotel extends Model
{
    // use SoftDeletes;

    protected $table = 'category_hotel';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';


    public function hotel()
    {
        // return $this->belongsToMany(Hotel::class);
        return $this->hasOne(Provider::class, 'id', 'hotel_id');
    }


    public function category()
    {
        // return $this->belongsToMany(Category::class);
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}
