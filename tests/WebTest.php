<?php

use Cti\Di\Manager;
use Cti\Core\Web;

class WebTest extends PHPUnit_Framework_TestCase
{
    public function testWeb()
    {
        $manager = new Manager;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/application-url/direct';
        $manager->create('Cti\Core\Web', array(
            'base' => '/application-url/'
        ));
    }
}
