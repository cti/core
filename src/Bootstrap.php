<?php

namespace Cti\Core;

use Cti\Di\Manager;
use Cti\Di\Reflection;

use Symfony\Component\Finder\Finder;

class Bootstrap
{
    function init(Manager $manager, ResourceLocator $locator)
    {
        $finder = new Finder();
        $finder->files()->name('*.php')->in($locator->path('src php Bootstrap'));
        foreach ($finder as $file) {
            $class = 'Bootstrap\\' . $file->getBasename('.php');
            $manager->get($class);
        }
    }
}