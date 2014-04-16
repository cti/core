<?php

namespace Cti\Core\Extension;

use Cti\Core\Application;
use Cti\Di\Locator;

class ConsoleExtension
{
    function init(Application $application, Locator $locator)
    {
        $locator->register('console', function($locator) use ($application) {
            
            // create application 
            $console = $locator->getManager()->get('Symfony\Component\Console\Application');            

            // add application commands
            foreach($application->getClasses('Command') as $class) {
                $console->add($locator->getManager()->get($class));
            }
            return $console;
        });        
    }
}