<?php

namespace Module;

use Build\Application;
use Cti\Core\Application\Bootstrap;

class Greet implements Bootstrap
{
    /**
     * @var string
     */
    protected $template;

    /**
     * bootstrap application
     * @param Application $application
     * @return mixed
     */
    public function boot(Application $application)
    {
        $this->template = 'Hello, %s';
    }

    /**
     * @param string $name
     * @return string
     */
    function hello($name)
    {
        return sprintf($this->template, $name);
    }
}