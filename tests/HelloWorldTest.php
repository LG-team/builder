<?php

use CSchmitz\HelloWorld\SayHello;

class SayHelloTest extends PHPUnit_Framework_TestCase
{

    public function testGreetings()
    {
        $sayhello = new SayHello;
        $this->assertEquals('Hello World, Composer!', $sayhello->world());
    }
}
