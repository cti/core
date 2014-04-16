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

            foreach ($application->getClasses('Controller') as $controller) {
                $configuration->push('Cti\Core\Web', 'controllers', $controller);
            }

            return $locator->getManager()->create('Cti\Core\Web');
        });
    }
}