<?php

namespace Cti\Core\Application;

use Build\Application;
use Cti\Core\Exception;
use Cti\Di\Manager;

/**
 * Class Factory
 * @package Cti\Core\Application
 */
class Factory
{
    /**
     * @param $root
     * @return Factory
     */
    static function create($root)
    {
        return new self($root);
    }

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var bool
     */
    protected $generate = true;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @param string $root
     */
    function __construct($root)
    {
        $this->manager = $manager = new Manager();

        if (is_string($root)) {
            $config = implode(DIRECTORY_SEPARATOR, array($root, 'resources', 'php', 'config.php'));
            $this->manager->getConfiguration()->load($config);

        } elseif (is_array($root)) {
            if(!isset($root['Cti\Core\Module\Project'])) {
                throw new Exception('Cti\Core\Module\Project configuration required');
            }

            $project = $root['Cti\Core\Module\Project'];
            if(!isset($project['path'])) {
                throw new Exception('Cti\Core\Module\Project.path property required');
            }

            $this->manager->getConfiguration()->load($root);

        } else {
            throw new Exception("Can't process factory  constructor param");

        }

        $manager->register($this);

        $factory = $manager->getConfiguration()->get(__CLASS__);

        if (isset($factory['generate'])) {
            $this->generate = $factory['generate'];
        }
    }

    /**
     * @return Application
     */
    function getApplication()
    {
        if (!isset($this->application)) {
            if ($this->generate) {
                $this->manager->create('Cti\Core\Application\Generator');
            }
            $this->application = $this->manager->create('Build\\Application');
        }
        return $this->application;
    }
}