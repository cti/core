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
        $locator->register('resource', 'Cti\Core\Resource');

        return $locator->getApplication();
    }

    /**
     * @inject
     * @var Cti\Di\Locator
     */
    protected $locator;

    protected $extensions = array(
        'Cti\Core\Extension\WebExtension',
        'Cti\Core\Extension\ConsoleExtension',
    );

    /**
     * call all extension methods
     * process all extension classes
     */
    function init()
    {
        // get current manager
        $manager = $this->locator->getManager();

        // process default extensions
        array_walk($this->extensions, array($manager, 'get'));

        // process application extension classes
        $extensions = $this->listClasses('Extension');
        array_walk($extensions, array($manager, 'get'));
    }

    /**
     * get class list
     */
    public function listClasses($namespace)
    {
        $classes = array();
        foreach($this->getLocator()->getResource()->listFiles("src php ".$namespace) as $file) {
            $classes[] = $namespace . '\\' . $file->getBasename('.php');
        }
        return $classes;
    }

    public function getLocator()
    {
        return $this->locator;
    }
}