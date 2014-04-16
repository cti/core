<?php

use Cti\Core\Application;

class ApplicationTest extends PHPUnit_Framework_TestCase
{

    function testBasics()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $config = array(
            'Cti\Core\Resource' => array(__DIR__)
        );

        $locator = Application::create($config)->getLocator();

        ob_start();
        $locator->getWeb()->run();
        $this->assertSame(ob_get_clean(), 'index page');
        
        $this->assertInstanceOf('Symfony\Component\Console\Application', $locator->getConsole());
    }
    
}
