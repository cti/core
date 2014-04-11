<?php

namespace Cti\Core;

use Cti\Di\Locator;
use Cti\Di\Manager;
use Cti\Di\Reflection;

class Application
{

    public static function create($config)
    {
        // create di
        $locator = new Locator;

        // load configuration
        $locator->getManager()->getConfiguration()->load($config);

        // register application service
        $locator->register('application', get_called_class());

        return $locator->getApplication();
    }

    /**
     * @inject
     * @var Cti\Di\Locator
     */
    protected $locator;

    /**
     * @inject
     * @var Cti\Core\Resource
     */
    protected $resource;

    /**
     * call all bootstrap methods
     * process all bootstrap classes
     */
    function init()
    {
        // process local bootstrap methods
        foreach(Reflection::getReflectionClass(get_class($this))->getMethods() as $method) {
            if(strpos($method->getName(), 'init') === 0 && strlen($method->getName())> 4) {
                $this->getManager()->call($this, $method->getName());
            }
        }

        // process application bootstrap classes
        $classes = $this->listClasses('Bootstrap');
        array_walk($classes, array($this->getManager(), 'get'));
    }

    /**
     * get class list
     */
    public function listClasses($namespace)
    {
        $classes = array();
        foreach($this->getResource()->listFiles("src php ".$namespace) as $file) {
            $classes[] = $namespace . '\\' . $file->getBasename('.php');
        }
        return $classes;
    }

    /**
     * init console service
     */
    public function initConsole()
    {
        $bootstrap = $this;
        $this->getLocator()->register('console', function($locator) use ($bootstrap) {

            // create application 
            $console = $locator->getManager()->get('Symfony\Component\Console\Application');            

            // add application commands
            foreach($bootstrap->listClasses('Command') as $class) {
                $console->add($locator->getManager()->get($class));
            }

            return $console;
        });
    }

    /**
     * init web service
     */
    public function initWeb()
    {
        $bootstrap = $this;
        $this->locator->register('web', function($locator) use ($bootstrap) {

            $locator->getManager()->getConfiguration()->set(
                'Cti\Core\Web', 'controllers', $bootstrap->listClasses('Controller')
            );

            return $locator->getManager()->create('Cti\Core\Web');
        });        
    }

    /**
     * @return Cti\Di\Locator
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * @return Cti\Di\Manager
     */
    public function getManager()
    {
        return $this->getLocator()->getManager();
    }

    /**
     * @return Cti\Core\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}