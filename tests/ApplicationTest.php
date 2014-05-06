<?php

use Cti\Core\Application\Factory;
use Symfony\Component\Filesystem\Filesystem;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
    function testGenerator()
    {
        $application = Factory::create(__DIR__)->getApplication();

        $this->assertInstanceOf('Build\Application', $application);

        // default modules
        $this->assertTrue(method_exists($application, 'getWeb'));
        $this->assertTrue(method_exists($application, 'getConsole'));
        $this->assertTrue(method_exists($application, 'getProject'));

        // project modules
        $this->assertTrue(method_exists($application, 'getAlias'));

        $this->assertInstanceOf('Module\Greet', $application->getAlias());
    }

    function testLocalConfig()
    {
        $app = Factory::create(__DIR__)->getApplication();

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
            'Cti\Core\Module\Project' => array(
                'path' => __DIR__
            ),
        );

        $application = Factory::create($config)->getApplication();

        ob_start();
        $application->getWeb()->run();
        $this->assertSame(ob_get_clean(), 'index page');

        $cache = $application->getProject()->getPath('build php cache.php');

        $fs = new Filesystem();
        $fs->dumpFile($cache, '<?php return array();');
    }

    function testCaching()
    {
        $config = array(
            'Cti\Core\Module\Project' => array(
                'path' => __DIR__
            )
        );

        $application = Factory::create($config)->getApplication();

        $console = $application->getConsole();
        $console->execute('deploy');

        $cache = $application->getProject()->getPath('build php cache.php');

        $this->assertFileExists($cache);

        $cachedApplication = $application;

        $this->assertGreaterThan(100, count($cachedApplication->getManager()->get('Cti\Di\Cache')->getData()));

        unlink($cache);

        $newApplication = Factory::create($config)->getApplication();
        $this->assertLessThan(100, count($newApplication->getManager()->get('Cti\Di\Cache')->getData()));

        $filesystem = new Filesystem();
        $filesystem->dumpFile($cache, '<?php return array();');
    }

    function testFailCreation()
    {
        $this->setExpectedException('Exception');
        Factory::create(2 * 2)->getApplication();
    }

    function testProjectPath()
    {
        $this->setExpectedException('Exception');
        Factory::create(array())->getApplication();
    }

    function testProjectConfig()
    {
        $this->setExpectedException('Exception');
        Factory::create(array('Cti\Core\Module\Project' => array()))->getApplication();
    }

    function testEmpty()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $config = array(
            'Cti\Core\Module\Project' => array(
                'path' => __DIR__
            )
        );

        $application = Factory::create($config)->getApplication();
        $application->getWeb();
        $application->getConsole();
    }
}
