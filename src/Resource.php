<?php

namespace Cti\Core;

use Symfony\Component\Finder\Finder;

/**
 * Resource locator
 * @package Cti\Core
 */
class Resource
{
    protected $locations;

    protected $project;
    protected $base;

    public function __construct($project)
    {
        $this->locations = array(
            $this->project = $project,
            $this->base = dirname(__DIR__)
        );
    }

    protected function parseArgs($args)
    {
        if(count($args) == 1) {
            $args = explode(' ', $args[0]);
        }
        return array_filter($args, 'strlen');
    }

    public function path()
    {
        $path = implode(DIRECTORY_SEPARATOR, $this->parseArgs(func_get_args()));
        foreach ($this->locations as $location) {
            $file = $location . DIRECTORY_SEPARATOR . $path;
            if (file_exists($file) || is_dir($file)) {
                return $file;
            }
        }
        return $this->project . DIRECTORY_SEPARATOR . $path;
    }

    public function project($path = '')
    {
        $path = implode(DIRECTORY_SEPARATOR, $this->parseArgs(func_get_args()));
        return $this->project . DIRECTORY_SEPARATOR . $path;
    }

    public function base($path = '')
    {
        $path = implode(DIRECTORY_SEPARATOR, $this->parseArgs(func_get_args()));
        return $this->base . DIRECTORY_SEPARATOR . $path;
    }

    public function listFiles($location)
    {
        $path = $this->path($location);
        if(!is_dir($path)) {
            return array();
        }
        $finder = new Finder();
        return $finder->files()->name('*.php')->in($path);
    }
}