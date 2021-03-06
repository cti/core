<?php

use Cti\Core\String;

class StringTests extends PHPUnit_Framework_TestCase
{
    
    public function testConvertToCamelCase()
    {
        $this->assertSame(String::convertToCamelCase('hello_world'), 'HelloWorld');
        $this->assertSame(String::convertToCamelCase('hello-world'), 'HelloWorld');
        $this->assertSame(String::convertToCamelCase('hello.world'), 'HelloWorld');
        $this->assertSame(String::convertToCamelCase('hello'), 'Hello');
    }

    public function testCamelCaseToUnderScore()
    {
        $this->assertSame(String::camelCaseToUnderScore('ThisIsTest'), 'this_is_test');
    }

    public function testPluralize()
    {
        $this->assertSame(String::pluralize('cat'), 'cats');
        $this->assertSame(String::pluralize('pony'), 'ponies');
        $this->assertSame(String::pluralize('bass'), 'basses');
        $this->assertSame(String::pluralize('case'), 'cases');
    }

    public function testFormatBytes()
    {
        $this->assertSame(String::formatBytes(1024), '1k');
        $this->assertSame(String::formatBytes(1024*1024*2), '2M');
        $this->assertSame(String::formatBytes(1024*1024*1024*4), '4G');
        $this->assertSame(String::formatBytes(1024*1024*1024*1024*5), '5T');
    }
}
