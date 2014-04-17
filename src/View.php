<?php

namespace Cti\Core;

use BadMethodCallException;
use Exception;
use OutOfRangeException;

/**
 * Template engine
 * @package Cti\Core
 */
class View
{
    /**
     * @var Cti\Core\Application 
     */
    protected $application;

    /**
     * @param Cti\Core\Application $application 
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * render template
     * @param string $name
     * @param array $data 
     * @return string 
     */
    public function render($name, $data = array())
    {
        try {
            extract($data);
            ob_start();
            include $this->application->getPath('resources php view '.func_get_arg(0).'.php');
            return ob_get_clean();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * show rendered template
     * @param string $name 
     * @param array $data 
     */
    public function show($name, $data = array()) 
    {
        echo $this->render($name, $data);
    }
}
