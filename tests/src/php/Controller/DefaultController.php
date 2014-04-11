<?php

namespace Controller;

class DefaultController
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