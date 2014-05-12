<?php

use Cti\Core\Application\Factory;
use Cti\Core\Module\Project;
use Cti\Core\Module\Fenom;

class FenomTest extends PHPUnit_Framework_TestCase
{
    function testDisplay()
    {
        $application = Factory::create(__DIR__)->getApplication();
        ob_start();
        $application->getFenom()->display('test');
        $result = ob_get_clean();
        $this->assertSame('Test!', $result);
    }

    function testSources()
    {
        $application = Factory::create(__DIR__)->getApplication();
        $application->getManager()->getInitializer()->after('Cti\Core\Module\Fenom', array($this, 'addSource'));
        $result = $application->getFenom()->render('hello', array('name' => 'World'));
        $this->assertSame($result, 'Hello, World!');
    }

    function addSource(Fenom $fenom, Project $project)
    {
        $fenom->addSource($project->getPath('resources another-fenom'));
    }

    function testNotFoundException()
    {
        $this->setExpectedException('Exception');
        Factory::create(__DIR__)->getApplication()->getFenom()->display('fail');
    }
}
