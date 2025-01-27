<?php

namespace App\Exceptions;

use Exception;

class UniqueNameException extends Exception
{
    public function __construct($message = "Já existe um registro com esse nome!")
    {
        parent::__construct($message);
    }
}
