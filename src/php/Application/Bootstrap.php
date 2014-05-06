<?php

namespace Cti\Core\Application;

use Build\Application;

/**
 * Interface Bootstrap
 * @package Cti\Core\Application
 */
interface Bootstrap
{
    /**
     * bootstrap application
     * @param Application $application
     * @return mixed
     */
    public function boot(Application $application);
}