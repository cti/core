<?php

namespace Cti\Core\Extension;

use Cti\Core\Application;
use Cti\Di\Reflection;

class ConsoleExtension
{
    function init(Application $application)
    {
        $application->register('console', function($application) {
            
            // create application 
            $console = $application->getManager()->get('Symfony\Component\Console\Application');

            $command = 'Symfony\Component\Console\Command\Command';

            // add application commands
            foreach($application->getClasses('Command') as $class) {
                if(Reflection::getReflectionClass($class)->isSubclassOf($command)) {
                    $console->add($application->getManager()->get($class));
                }
            }

            $console->add($application->getManager()->get('Cti\Core\Command\BuildCache'));

            return $console;
        });
    }
}