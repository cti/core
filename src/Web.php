<?php

namespace Cti\Core;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Web application implementation
 * @package Cti\Core
 */
class Web
{

    /**
     * @var string base url
     */
    public $base = '/';

    /**
     * @var string
     */
    public $method;

    /**
     * @inject
     * @var Cti\Di\Manager
     */
    protected $manager;

    /**
     * controller mapping
     */
    protected $controllers = array();

    public function add($location, $controller)
    {
        if($location[0] != '/') {
            throw new Exception(sprintf("Incorrect location (%s) must starts with /", $location));
        }
        if($location[strlen($location)-1] != '/') {
            $location .= '/';
        }
        $this->controllers[$location] = $controller;
    }

    public function init()
    {
        // validate base
        if ($this->base != '/') {
            if ($this->base[0] != '/') {
                throw new Exception('base property not begins with /');
            } elseif ($this->base[strlen($this->base) - 1] != '/') {
                throw new Exception('base property not ends with /');
            }
        }

        // define method
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        // define location
        $this->location = '/' . substr($_SERVER['REQUEST_URI'], strlen($this->base));
        if($this->location[strlen($this->location)-1] != '/') {
            $this->location .= '/';
        }
    }

    public function process()
    {
        $mount = array();
        foreach($this->controllers as $path => $controllerName) {
            $mount[$path] = strlen($path);
        }
        arsort($mount);

        $controller = null;

        foreach (array_keys($mount) as $path) {
            if(strpos($this->location, $path) === 0) {
                $controller = $this->controllers[$path];
                $chain = explode('/', substr($this->location, strlen($path)));
                break;
            }
        }

        if(!$controller) {
            throw new Exception(sprintf("No controller can process url: %s", $this->location));
        }

        foreach ($chain as $k => $v) {
            if ($v === '') {
                unset($chain[$k]);
            }
        }
        $chain = array_values($chain);

        try {

            $slug = count($chain) ? array_shift($chain) : '';
            $method = $this->method . String::convertToCamelCase($slug);

            if(method_exists($controller, $method)) {
                $result = $this->manager->call($controller, $method, array_merge($chain, array(
                    'chain' => $chain
                )));
            } elseif(method_exists($controller, 'processChain')) {
                if($slug != 'index') {
                    array_unshift($chain, $slug);
                }
                $result = $this->manager->call($controller, 'processChain', array(
                    'chain' => $chain
                ));
            } else {
                throw new Exception("Not found", 404);
            }


        } catch(Exception $e) {
            if(!method_exists($controller, 'processException')) {
                throw $e;
            }
            $result = $this->manager->call($controller, 'processException', array($e, 'exception' => $e));
        }

        echo $result;
    }

    /**
     * generate relative aplication url
     * @param  string $location 
     * @return string
     */
    function getUrl($location = '')
    {
        return $this->base . implode('/', func_get_args());
    }
}