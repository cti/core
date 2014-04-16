<?php

namespace Cti\Core\Extension;

use Cti\Core\Application;

class WebExtension
{
    function init(Application $application)
    {
        $application->register('web', function($application) {

            $configuration = $application->getManager()->getConfiguration();

            foreach ($application->getClasses('Controller') as $controller) {
                $configuration->push('Cti\Core\Web', 'controllers', $controller);
            }

            return $application->getManager()->create('Cti\Core\Web');
        });
    }
}