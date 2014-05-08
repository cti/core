<?php

namespace Cti\Core\Application;

use Build\Application;

/**
 * Interface Bootstrap
 * @package Cti\Core\Application
 */
interface Bootloader
{
    /**
     * bootstrap application
     * @param Application $application
     * @return mixed
     */
    public function boot(Application $application);
}