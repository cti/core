<?php

use Cti\Core\Application;
use Cti\Core\Barista;
use Cti\Core\Module\Coffee;

class BaristaTest extends PHPUnit_Framework_TestCase
{
    function testCompilation()
    {
        $barista = $this->getCoffee();
        $this->assertFileExists($barista->build('test'));
    }

    function testClassSourceFail()
    {
        $this->setExpectedException('Exception');
        $this->getCoffee()->getClassSource('Acme');
    }

    function testLocalPathFail()
    {
        $this->setExpectedException('Exception');
        $this->getCoffee()->getLocalPath('Acme');
    }

    function testDependencyListFail()
    {
        $this->setExpectedException('Exception');
        $this->getCoffee()->build('Error');
    }

    /**
     * @return Coffee
     */
    protected function getCoffee()
    {
        $config = array(
            'Cti\Core\Module\Project' => array(
                'path' => __DIR__
            ),
        );

        return Application\Factory::create($config)->getApplication()->getCoffee();
    }
}

 