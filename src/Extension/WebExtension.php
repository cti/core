<?php

namespace Cti\Core\Extension;

use Cti\Core\Application;
use Cti\Di\Locator;

class WebExtension
{
    function init(Application $application, Locator $locator)
    {
        $locator->register('web', function($locator) use ($application) {

            $configuration = $locator->getManager()->getConfiguration();

            $controllers = $configuration->get('Cti\Core\Web', 'controllers', array());
            foreach ($application->listClasses('Controller') as $controller) {
                $controllers[] = $controller;
            }
            $controllers = $configuration->set('Cti\Core\Web', 'controllers', $controllers);

            return $locator->getManager()->create('Cti\Core\Web');
        });        
    }
}