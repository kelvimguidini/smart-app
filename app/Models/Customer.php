<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'logo',
        'document',
        'phone',
        'email',
        'color',
        'responsibleAuthorizing',
    ];
    protected $table = 'customer';

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
