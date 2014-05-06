<?php

namespace Cti\Core\Application;

use Build\Application;

interface Bootstrap
{
    public function boot(Application $application);
}