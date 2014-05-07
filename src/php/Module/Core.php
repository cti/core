<?php

namespace Cti\Core\Module;

/**
 * Class Core
 * @package Cti\Core\Module
 */
class Core extends Project
{
    /**
     * init core project path
     */
    public function init(Cache $cache)
    {
        parent::init($cache);
        $this->path = dirname(dirname(dirname(__DIR__)));
        $this->prefix = 'Cti\\Core\\';
    }


    /**
     * @return array
     */
    protected function getAvailableNamespaces()
    {
        return array('Application', 'Command', 'Module');
    }
}