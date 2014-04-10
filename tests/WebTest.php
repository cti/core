<?php

use Cti\Di\Manager;
use Cti\Core\Web;

class WebTest extends PHPUnit_Framework_TestCase
{
    protected function createWeb($base)
    {
        if(!isset($_SERVER['REQUEST_METHOD'])) {
            // defaults
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['REQUEST_URI'] = '/';
        }
        $manager = new Manager();
        return $manager->create('Cti\Core\Web', array(
            'base' => $base
        ));
    }
    public function testBaseUrlStartFail()
    {
        $this->setExpectedException('Exception');
        $this->createWeb('first-symbol-fail');
    }
    
    public function testBaseUrlEndFail()
    {
        $this->setExpectedException('Exception');
        $this->createWeb('/last-symbol-fail');
    }

    public function testControllerUrlFail()
    {
        $this->setExpectedException('Exception');
        $this->createWeb('/')->add('my', __CLASS__);
    }
    
    public function testControllerUrlComplete()
    {
        $web = new Web;
        $web->add('/my', __CLASS__);

        $this->setExpectedException('Exception');
        $web->add('/my/', __CLASS__);
    }

    public function testUrl()
    {
        $manager = new Manager;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/application-url/direct';

        $web = $manager->create('Cti\Core\Web', array(
            'base' => '/application-url/'
        ));

        $this->assertSame($web->getUrl('test'), '/application-url/test');
    }

    public function testProcessing()
    {
        $_SERVER['REQUEST_URI'] = '/';

        $web = $this->createWeb('/');
        $web->add('/', 'Common\Controller');

        ob_start();
        $web->process();
        $this->assertSame(ob_get_clean(), 'index page');
    }

    public function testParameters()
    {
        $_SERVER['REQUEST_URI'] = '/hello/nekufa';

        $web = $this->createWeb('/');
        $web->add('/', 'Common\Controller');

        ob_start();
        $web->process();
        $this->assertSame(ob_get_clean(), 'Hello nekufa!');
    }

    public function testMethodChange()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/upload';

        $web = $this->createWeb('/');
        $web->add('/', 'Common\Controller');

        ob_start();
        $web->process();
        $this->assertSame(ob_get_clean(), 'uploading');
    }

    public function testChainProcessing()
    {
        $_SERVER['REQUEST_URI'] = '/a/b/c';

        $web = $this->createWeb('/');
        $web->add('/', 'Common\Controller');

        ob_start();
        $web->process();
        $this->assertSame(ob_get_clean(), json_encode(explode(' ', 'a b c')));
    }

    public function testExceptionController()
    {
        $manager = new Manager;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/t/';

        $web = $this->createWeb('/t/');
        $web->add('/', 'Common\ExceptionHandlingController');

        ob_start();
        $web->process();
        $this->assertSame(ob_get_clean(), json_encode(explode(' ', 'a b c')));
    }

    public function testNoController()
    {
        $manager = new Manager;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/t/';

        $web = $this->createWeb('/t/');

        $this->setExpectedException('Exception');
        $web->process();
    }

    public function testNotFound()
    {
        $manager = new Manager;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/t/';

        $web = $this->createWeb('/t/');
        $web->add('/', __CLASS__);

        $this->setExpectedException('Exception');
        $web->process();
    }
}
