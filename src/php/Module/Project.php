<?php

namespace Cti\Core\Module;

use Build\Application;
use Cti\Core\Application\Warmer;
use Cti\Core\Exception;
use Symfony\Component\Finder\Finder;

/**
 * Class Project
 * @package Cti\Core\Module
 */
class Project implements Warmer
{
    /**
     * @var string
     */
    public $path;

    /**
     * namespace prefix
     * @var string
     */
    public $prefix;

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * @param Cache $cache
     */
    public function init(Cache $cache)
    {
        if($cache->exists(get_class($this))) {
            $this->cache = $cache->get(get_class($this));
        }
    }

    /**
     * get project class list
     * @param string $namespace
     * @return array
     */
    public function getClasses($namespace)
    {
        if(!isset($this->cache[__METHOD__])) {
            $this->cache[__METHOD__] = array();
        }
        if(isset($this->cache[__METHOD__][$namespace])) {
            return $this->cache[__METHOD__][$namespace];
        }

        if(!in_array($namespace, $this->getAvailableNamespaces())) {
            throw new Exception(sprintf('Namespace %s not available for %s', $namespace, get_class($this)));
        }

        $result = array();
        $path = $this->getPath("src php $namespace");

        if(is_dir($path)) {
            $finder = new Finder();
            foreach($finder->files()->name('*.php')->in($path) as $file) {
                $result[] = $this->prefix . $namespace . '\\' . $file->getBasename('.php');
            }
        }

        return $this->cache[__METHOD__][$namespace] = $result;
    }

    /**
     * get project path
     * @param string $string
     * @return string
     */
    public function getPath($string = '')
    {
        $args = func_get_args();
        if(count($args) == 1) {
            $args = explode(' ', $args[0]);
        }

        $args = array_filter($args, 'strlen');
        array_unshift($args, $this->path);

        $result = implode(DIRECTORY_SEPARATOR, $args);

        return $result;
    }

    /**
     * warm application
     * @param Application $application
     * @return mixed
     */
    public function warm(Application $application)
    {
        $cache = $application->getCache();
        foreach ($this->getAvailableNamespaces() as $namespace) {
            $this->getClasses($namespace);
        }
        $cache->set(get_class($this), $this->cache);
    }

    /**
     * @return array
     */
    protected function getAvailableNamespaces()
    {
        return array('Controller', 'Command', 'Direct');
    }
}