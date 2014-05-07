<?php

namespace Cti\Core\Module;

use Symfony\Component\Finder\Finder;

/**
 * Class Project
 * @package Cti\Core\Module
 */
class Project
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
     * get project class list
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
                $classes[] = $this->prefix . $namespace . '\\' . $file->getBasename('.php');
            }
        }

        return $classes;
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
}