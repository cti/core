<?php

use Cti\Di\Manager;
use Cti\Core\Bootstrap;

class BootstrapTest extends PHPUnit_Framework_TestCase
{
    function testBootstrap()
    {
        $resource = $this->getMockBuilder('Cti\Core\ResourceLocator')
            ->disableOriginalConstructor()
            ->setMethods(array('path'))
            ->getMock();

        $manager = $this->getMockBuilder('Cti\Di\Manager')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();

        $resource->expects($this->once())->method('path')
            ->will($this->returnValue(__DIR__ . DIRECTORY_SEPARATOR . 'Bootstrap'));

        $manager->expects($this->once())->method('get')->with('Bootstrap\Database');

        $bootstrap = new Bootstrap;
        $bootstrap->init($manager, $resource);
    }
}
