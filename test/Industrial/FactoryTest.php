<?php

require_once "src/Industrial/Module.php";
require_once "src/Industrial/Binder.php";
require_once "src/Industrial/Factory.php";

class Industrial_FactoryTest extends PHPUnit_Framework_TestCase
{
    public function testModuleLoad()
    {
        $factory = new \Industrial\Factory(new TestModule);
        $obj = $factory->make(TestClass1::$class);
        $this->assertTrue($obj instanceof TestClass1::$class, "Failed to create class");
    }

    public function testBindClassAfter()
    {
        $factory = new \Industrial\Factory(new TestModule);
        $factory->addBound(new \Industrial\Binder(TestClass2::$class));
        $obj = $factory->make(TestClass2::$class);
        $this->assertTrue($obj instanceof TestClass2::$class, "Failed to create class");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNoClass()
    {
        $factory = new \Industrial\Factory(new TestModule);
        $factory->make("NotAClass");
    }
}

class TestModule extends \Industrial\Module
{
    protected function config()
    {
        $this->bind(TestClass1::$class);
    }
}

class TestClass1
{
    public static $class = "TestClass1";
}

class TestClass2
{
    public static $class = "TestClass2";
}
