<?php

namespace Cti\Core\Module;

use Build\Application;
use Cti\Core\Exception;
use Cti\Core\String;
use Cti\Di\Reflection;

/**
 * Web module
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
     * @var Manager
     */
    protected $manager;

    /**
     * controller mapping
     */
    protected $controllers = array();


    /**
     * collect controllers list
     * @param Project $project
     * @throws \Cti\Core\Exception
     */
    public function init(Project $project)
    {
        foreach($project->getClasses('Controller') as $controller) {
            $location = '/';
            $name = Reflection::getReflectionClass($controller)->getShortName();
            $slug = String::camelCaseToUnderScore(substr($name, 0, -1 * strlen('Controller')));
            if($slug != 'default') {
                $location = '/' . $slug;
            }
            $this->add($location, $controller);
        }

        // validate base
        if ($this->base != '/') {
            if ($this->base[0] != '/') {
                throw new Exception('Base url not begins with /');
            } elseif ($this->base[strlen($this->base) - 1] != '/') {
                throw new Exception('Base url not ends with /');
            }
            if(strpos($_SERVER['REQUEST_URI'], $this->base) !== 0) {
                throw new Exception(sprintf('Base url (%s) is incorrect', $this->base));
            }
        }

        // define method
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        // define location
        $this->location = '/' . substr($_SERVER['REQUEST_URI'], strlen($this->base));
        if($this->location[strlen($this->location)-1] != '/') {
            $this->location .= '/';
        }

        // process controllers
        $controllers = $this->controllers;
        $this->controllers = array();
        foreach ($controllers as $path => $controller) {
            if(is_numeric($path)) {
                $class = Reflection::getReflectionClass($controller)->getShortName();
                if(substr($class, -10) == 'Controller') {
                    $class = substr($class,0, -10);
                    $slug = String::camelCaseToUnderScore($class);
                    if($slug == 'default' || !$slug) {
                        $path = '/';
                    } else {
                        $path = '/' . $slug .'/';
                    }
                }
            }
            $this->add($path, $controller);
        }
    }

    /**
     * mount controller
     * @param $location
     * @param $controller
     * @throws \Cti\Core\Exception
     */
    public function add($location, $controller)
    {
        if($location[0] != '/') {
            throw new Exception(sprintf("Incorrect location (%s) must starts with /", $location));
        }
        if($location[strlen($location)-1] != '/') {
            $location .= '/';
        }
        if(isset($this->controllers[$location])) {
            throw new Exception(sprintf("Duplicate location %s", $location));
        }
        $this->controllers[$location] = $controller;
    }

    /**
     * run web processor
     * @throws \Cti\Core\Exception
     * @throws \Exception
     */
    public function run()
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

        $chain = array_values(array_filter($chain, 'strlen'));

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
     * generate relative application url
     * @param  string $location
     * @return string
     */
    function getUrl($location = '')
    {
        return $this->base . implode('/', func_get_args());
    }
}