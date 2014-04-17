<?php

use Cti\Core\Application;

class ApplicationTest extends PHPUnit_Framework_TestCase
{

    function testBasics()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $config = array(
            'Cti\Core\Application' => array(
                'path' => __DIR__
            ),
        );

        $application = Application::create($config);
        
        ob_start();
        $application->getWeb()->run();
        $this->assertSame(ob_get_clean(), 'index page');
        
        $this->assertInstanceOf('Symfony\Component\Console\Application', $application->getConsole());
    }

    function testEmpty()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $config = array(
            'Cti\Core\Application' => array( 
                'path' => dirname(__DIR__)
            )
        );

        $application = Application::create($config);
        $application->getWeb();
        $application->getConsole();
    }
    
}
