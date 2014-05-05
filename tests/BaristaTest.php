<?php

use Cti\Core\Application;
use Cti\Core\Barista;

class BaristaTest extends PHPUnit_Framework_TestCase
{
    function testCompilation()
    {
        $barista = $this->getBarista();
        $this->assertFileExists($barista->build('test'));
    }

    function testClassSourceFail()
    {
        $this->setExpectedException('Exception');
        $this->getBarista()->getClassSource('Acme');
    }

    function testLocalPathFail()
    {
        $this->setExpectedException('Exception');
        $this->getBarista()->getLocalPath('Acme');
    }

    function testDependencyListFail()
    {
        $this->setExpectedException('Exception');
        $this->getBarista()->build('Error');
    }

    /**
     * @return Barista
     */
    protected function getBarista()
    {
        $config = array(
            'Cti\Core\Module\Project' => array(
                'path' => __DIR__
            ),
        );

        return Application\Factory::create($config)->getApplication()->getCoffee();
    }
}

 