<?php

use Cti\Core\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;

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

        $cache = $application->getPath('build php cache.php');

        $fs = new Filesystem();
        $fs->dumpFile($cache, '<?php return array();');

        /**
         * @var \Symfony\Component\Console\Application $console
         */
        $console = $application->getConsole();
        $command = $console->find("build:cache");

        $input = new ArrayInput(array(
            'command' => 'build:cache',
        ));

        $output = new NullOutput;
        $command->run($input, $output);

        $this->assertFileExists($cache);

        $cachedApplication = Application::create($config);

        $this->assertGreaterThan(100, count($cachedApplication->getManager()->get('Cti\Di\Cache')->getData()));

        unlink($cache);

        $newApplication = Application::create($config);
        $this->assertLessThan(100, count($newApplication->getManager()->get('Cti\Di\Cache')->getData()));

        $fs->dumpFile($cache, '<?php return array();');
    }

    function testFailCreation()
    {
        $this->setExpectedException('Exception');
        Application::create(2*2);
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
