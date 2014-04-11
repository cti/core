<?php

use Cti\Core\Resource;
use Cti\Core\String;
use Cti\Core\View;
use Cti\Core\Web;
use Cti\Di\Manager;

class ToolsTests extends PHPUnit_Framework_TestCase
{
    function testResource()
    {
        $l = new Resource(__DIR__);

        // file that exists in this project
        $this->assertSame($l->path('ToolsTests.php'), __FILE__);
        $this->assertSame(
            $l->base('ToolsTests.php'),
            implode(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'ToolsTests.php'))
        );

        // file that exists in the base
        $reflection = new ReflectionClass('Cti\Core\Resource');
        $this->assertSame($l->path('src Resource.php'), $reflection->getFileName());

        // project location 
        $this->assertSame(
            $l->project('src Tools Resource.php'), 
            implode(DIRECTORY_SEPARATOR, array(__DIR__, 'src', 'Tools', 'Resource.php'))
        );

        // ignore duplicate spaced 
        $this->assertSame(
            $l->project('a b'), 
            $l->project('a  b')
        );

        // working with array 
        $this->assertSame(
            $l->project('a', 'b'), 
            $l->project('a  b')
        );

        // not exists file - project location
        $this->assertSame($l->path('no-file'), __DIR__ . DIRECTORY_SEPARATOR . 'no-file');

        $this->assertSame($l->listFiles("src php Bootstrap")->count(), 1);
    }

    function testView()
    {
        $view = new View(new Resource(__DIR__));
        ob_start();
        $view->show('test');
        $this->assertSame($view->render('test'), ob_get_clean());

        $this->assertSame('exception', $view->render('exception', array('test' => true)));
    }

}
