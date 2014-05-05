<?php

namespace Cti\Core\Module;

use Cti\Di\Manager;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class Console extends Application
{
    function init(Core $core, Project $project, Manager $manager)
    {
        foreach ($project->getClasses('Command') as $class) {
            $this->add($manager->get($class));
        }

        foreach ($core->getClasses('Command') as $class) {
            $this->add($manager->get('Cti\\Core\\' . $class));
        }
    }

    function execute($command, $input = array(), $output = null)
    {
        $input['command'] = $command;

        $instance = $this->find($input['command']);

        $input = new ArrayInput($input);

        if(!$output) {
            $output = new NullOutput();
        }

        $instance->run($input, $output);
    }
}