<?php

namespace Module;

class Greet
{
    /**
     * @param string $name
     * @return string
     */
    function hello($name)
    {
        return sprintf('Hello, %s!', $name);
    }
}