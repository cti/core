<?php

namespace Cti\Core\Module;

use Cti\Di\Manager;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Class Console
 * @package Cti\Core\Module
 */
class Console extends Application
{
    /**
     * Initialize console application
     * @param Core $core
     * @param Project $project
     * @param Manager $manager
     * @throws \Cti\Di\Exception
     */
    function init(Core $core, Project $project, Manager $manager)
    {
        foreach ($project->getClasses('Command') as $class) {
            $this->add($manager->get($class));
        }

        foreach ($core->getClasses('Command') as $class) {
            $this->add($manager->get('Cti\\Core\\' . $class));
        }
    }

    /**
     * command execute shortcut
     * @param $command
     * @param array $input
     * @param null $output
     * @throws \Exception
     */
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