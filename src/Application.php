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
        $manager = $this->locator->getManager();

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
        $this->locator->getManager()->get($extension);
        return $this;
    }

    /**
     * get class list
     */
    public function getClasses($namespace)
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