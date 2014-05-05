<?php

namespace Cti\Core\Application;

use Build\Application;
use Cti\Di\Manager;

class Factory
{
    /**
     * @param $config
     * @return Factory
     */
    static function create($config)
	{
		return new self($config);
	}

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var bool
     */
    protected $force = true;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @param string $config
     */
    function __construct($config)
	{
        $this->manager = new Manager();
        $this->manager->getConfiguration()->load($config);
        $this->manager->register($this);

        $factory = $this->manager->getConfiguration()->get(__CLASS__);

        if(isset($factory['force'])) {
            $this->force = $factory['force'];
        }
	}

    /**
     * @return Application
     */
    function getApplication()
    {
        if(!isset($this->application)) {
            if($this->force) {
                $this->manager->create('Cti\Core\Application\Generator');
            }
            $this->application = $this->manager->create('Build\\Application');
        }
        return $this->application;
    }
}