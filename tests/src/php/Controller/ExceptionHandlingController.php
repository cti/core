<?php

namespace Controller;

use Exception;

class ExceptionHandlingController
{

    function processException(Exception $e)
    {
        return $e->getMessage();
    }
}