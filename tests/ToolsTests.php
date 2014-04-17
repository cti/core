<?php

use Cti\Core\Application;
use Cti\Core\String;
use Cti\Core\View;
use Cti\Core\Web;
use Cti\Di\Manager;

class ToolsTests extends PHPUnit_Framework_TestCase
{
    function testView()
    {
        $app = Application::create(__DIR__ . '/resources/php/config.php');
        $view = new View($app);
        ob_start();
        $view->show('test');
        $this->assertSame($view->render('test'), ob_get_clean());

        $this->assertSame('exception', $view->render('exception', array('test' => true)));
    }

}
