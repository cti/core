<?php

namespace Cti\Core\Module;

use Cti\Di\Manager;
use Cti\Di\Reflection;
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
     * @inject
     * @var Manager
     */
    protected $manager;

    /**
     * Initialize console application
     * @param Core $core
     * @param Project $project
     * @throws \Cti\Di\Exception
     */
    function init(Core $core, Project $project)
    {
        $commands = array_merge($project->getClasses('Command'), $core->getClasses('Command'));

        array_walk($commands, array($this, 'processClass'));
    }

    /**
     * add class if it is console command
     * @param $class
     */
    function processClass($class)
    {
        if(Reflection::getReflectionClass($class)->isSubclassOf('Symfony\Component\Console\Command\Command')) {
            $this->add($this->getManager()->get($class));
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

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }
}