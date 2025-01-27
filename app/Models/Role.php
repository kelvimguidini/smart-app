<?php

namespace App\Models;

use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Role extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UniqueNameTrait;

    protected $fillable = ['name', 'active'];
    protected $table = 'roles';

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
     * @var bool
     */
    protected $active = 'active';

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }
}
