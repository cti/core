<?php

use Cti\Core\Module\Web;
use Cti\Di\Manager;

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
        return $manager->create('Cti\Core\Module\Web', array(
            'base' => $base
        ));
    }

    public function testControllerMount()
    {
        // defaults
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $manager = new Manager;
        $manager->setConfigureAllProperties(true);
        $web = $manager->create('Cti\Core\Module\Web', array(
            'base' => '/',
            'controllers' => array(
                'Controller\DefaultController'
            )
        ));

        ob_start();
        $web->run();

        $this->assertSame(ob_get_clean(), 'index page');
    }

    public function testNamedControllerMount()
    {
        // defaults
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/other';

        $manager = new Manager;
        $manager->setConfigureAllProperties(true);        
        $web = $manager->create('Cti\Core\Module\Web', array(
            'base' => '/',
            'controllers' => array(
                'Controller\DefaultController',
                'Controller\OtherController'
            )
        ));

        ob_start();
        $web->run();

        $this->assertSame(ob_get_clean(), 'other page');
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

    public function testBaseUrlVsRequest()
    {
        $_SERVER['REQUEST_URI'] = '/other';
        $this->setExpectedException('Exception');
        $this->createWeb('/fail/');
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

        $web = $manager->create('Cti\Core\Module\Web', array(
            'base' => '/application-url/'
        ));

        $this->assertSame($web->getUrl('test'), '/application-url/test');
    }

    public function testProcessing()
    {
        $_SERVER['REQUEST_URI'] = '/';

        $web = $this->createWeb('/');
        $web->add('/', 'Controller\DefaultController');

        ob_start();
        $web->run();
        $this->assertSame(ob_get_clean(), 'index page');
    }

    public function testParameters()
    {
        $_SERVER['REQUEST_URI'] = '/hello/nekufa';

        $web = $this->createWeb('/');
        $web->add('/', 'Controller\DefaultController');

        ob_start();
        $web->run();
        $this->assertSame(ob_get_clean(), 'Hello nekufa!');
    }

    public function testMethodChange()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/upload';

        $web = $this->createWeb('/');
        $web->add('/', 'Controller\DefaultController');

        ob_start();
        $web->run();
        $this->assertSame(ob_get_clean(), 'uploading');
    }

    public function testChainProcessing()
    {
        $_SERVER['REQUEST_URI'] = '/a/b/c';

        $web = $this->createWeb('/');
        $web->add('/', 'Controller\DefaultController');

        ob_start();
        $web->run();
        $this->assertSame(ob_get_clean(), json_encode(explode(' ', 'a b c')));
    }

    public function testExceptionController()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/t/';

        $web = $this->createWeb('/t/');
        $web->add('/', 'Controller\ExceptionHandlingController');

        ob_start();
        $web->run();
        $this->assertSame(ob_get_clean(), 'Not found');
    }

    public function testNoController()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/t/';

        $web = $this->createWeb('/t/');

        $this->setExpectedException('Exception');
        $web->run();
    }

    public function testNotFound()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/t/';

        $web = $this->createWeb('/t/');
        $web->add('/', __CLASS__);

        $this->setExpectedException('Exception');
        $web->run();
    }
}
