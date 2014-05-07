<?php

namespace Cti\Core\Application;

use Build\Application;

/**
 * Interface Bootstrap
 * @package Cti\Core\Application
 */
interface Warm
{
    /**
     * warm application
     * @param Application $application
     * @return mixed
     */
    public function warm(Application $application);
}