<?php

namespace Mmeshkatian\Ariel;

use Exception;

class ApiException extends Exception
{
    public $msg = '';
    public $status = 422;
    public $extraDetails = [];

    public function __construct($msg = null,$status = 422,$extraDetails = [])
    {
        parent::__construct($msg ?? 'Somthing Bad Happend.');

        $this->msg = $msg;
        $this->status = $status;
        $this->extraDetails = $extraDetails;
    }

}
