<?php

namespace Cti\Core;

use Cti\Di\Locator;
use Cti\Di\Manager;

class Application extends Locator
{

    public static function create($config)
    {
        $class = get_called_class();

        // create di
        $application = new $class();

        // load configuration
        $application->getManager()->getConfiguration()->load($config);

        // register application service
        $application->register('resource', 'Cti\Core\Resource');
        
        // init
        $application->init();

        return $application;
    }

    protected $extensions = array(
        'Cti\Core\Extension\ConsoleExtension',
        'Cti\Core\Extension\WebExtension',
    );

    /**
     * call all extension methods
     * process all extension classes
     */
    function init()
    {
        // get current manager
        $manager = $this->getManager();

        // process default extensions
        array_walk($this->extensions, array($this, 'inject'));

        // process application extension classes
        $extensions = $this->getClasses('Extension');
        array_walk($extensions, array($this, 'inject'));
    }

    /**
     * inject application extension
     * @param string $extension
     * @return Cti\Core\Application
     */
    function inject($extension)
    {
        if(!in_array($extension, $this->extensions)) {
            array_push($this->extensions, $extension);
        }
        $this->getManager()->get($extension);
        return $this;
    }

    /**
     * get class list
     */
    public function getClasses($namespace)
    {
        $classes = array();
        foreach($this->getResource()->listFiles("src php ".$namespace) as $file) {
            $classes[] = $namespace . '\\' . $file->getBasename('.php');
        }
        return $classes;
    }
}