<?php

namespace Cti\Core\Extension;

use Cti\Core\Application;

class ConsoleExtension
{
    function init(Application $application)
    {
        $application->register('console', function($application) {
            
            // create application 
            $console = $application->getManager()->get('Symfony\Component\Console\Application');            

            // add application commands
            foreach($application->getClasses('Command') as $class) {
                $console->add($application->getManager()->get($class));
            }
            return $console;
        });        
    }
}