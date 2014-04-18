<?php

use Cti\Core\Application;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
    function testLocalConfig()
    {
        $config = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'resources', 'php', 'config.php'));
        $app = Application::create($config);

        $configuration = $app->getManager()->getConfiguration();

        // configuration loaded
        $this->assertSame($configuration->get('class', 'property'), 'value');

        // local override
        $this->assertSame($configuration->get('class', 'property2'), 'new_value');
    }

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
