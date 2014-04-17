<?php

namespace Cti\Core;

use Cti\Di\Configuration;
use Cti\Di\Locator;
use Cti\Di\Manager;

use Symfony\Component\Finder\Finder;

class Application extends Locator
{
    protected $path;

    public static function create($config)
    {
        $class = get_called_class();

        if(is_string($config)) {
            $path = dirname(dirname(dirname($config)));
        } elseif(is_array($config) && isset($config[$class]) && isset($config[$class]['path'])) {
            $path = $config[$class]['path'];
        } else {
            throw new \Exception("Application creation fail");
        }

        $configuration = new Configuration(array(
            $class => array(
                'path' => $path
            ),
            'Cti\Di\Locator' => $class
        ));
        $configuration->load($config);

        $manager = new Manager($configuration);

        return $manager->get('Cti\Di\Locator');
    }

    protected $extensions = array(
        'Cti\Core\Extension\ConsoleExtension',
        'Cti\Core\Extension\WebExtension',
    );

    /**
     * call all extension methods
     * process all extension classes
     */
    function init(Manager $manager)
    {
        // process default extensions
        array_walk($this->extensions, array($this, 'extend'));

        // process application extension classes
        $extensions = $this->getClasses('Extension');
        array_walk($extensions, array($this, 'extend'));
    }

    /**
     * extend application with extension
     * @param string $extension
     * @return Cti\Core\Application
     */
    function extend($extension)
    {
        if(!in_array($extension, $this->extensions)) {
            array_push($this->extensions, $extension);
        }
        $this->getManager()->get($extension);
        return $this;
    }

    /**
     * get class list
     * @param string $namespace
     * @return array
     */
    public function getClasses($namespace)
    {
        $classes = array();
        $path = $this->getPath("src php $namespace");

        if(is_dir($path)) {
            $finder = new Finder();
            foreach($finder->files()->name('*.php')->in($path) as $file) {
                $classes[] = $namespace . '\\' . $file->getBasename('.php');
            }            
        }

        return $classes;
    }

    /**
     * get application path
     * @param string $string
     * @return string
     */
    public function getPath($string)
    {
        $args = func_get_args();
        if(count($args) == 1) {
            $args = explode(' ', $args[0]);
        }

        $args = array_filter($args, 'strlen');
        array_unshift($args, $this->path);

        return implode(DIRECTORY_SEPARATOR, $args);

    }
}