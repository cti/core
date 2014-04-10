<?php

namespace Common;

class Controller
{

    function get()
    {
        return 'index page';
    }

    function getHello($name)
    {
        return sprintf("Hello %s!", $name);
    }

    function postUpload()
    {
        return 'uploading';
    }

    function processChain($chain)
    {
        return json_encode($chain);
    }
    
}