<?php

namespace Cti\Core\Module;

class Core extends Project
{
    /**
     * @var string
     */
    public $path;

    public function init()
    {
        $this->path = dirname(dirname(dirname(__DIR__)));
    }
}