<?php

namespace App\Models;

use App\Traits\Activatable;
use App\Traits\UniqueNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Customer extends Model
{
    use SoftDeletes;
    use UniqueNameTrait;
    use Activatable;

    protected $fillable = [
        'name',
        'logo',
        'document',
        'phone',
        'email',
        'color',
        'responsibleAuthorizing',
        'active'
    ];
    protected $table = 'customer';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $id = 'id';


    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $color = 'color';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $document = 'document';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $phone = 'phone';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $email = 'email';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $responsibleAuthorizing = 'responsibleAuthorizing';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $logo = 'logo';

    public function crds()
    {
        return $this->belongsTo(CRD::class);
    }
}
