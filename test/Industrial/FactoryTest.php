<?php

require_once "test/Autoload.php";

class Industrial_FactoryTest extends PHPUnit_Framework_TestCase
{
    public function testModuleLoad()
    {
        $factory = new \Industrial\Factory(new TestModule);
        $obj = $factory->make(FactoryTestClass1::$class);
        $this->assertTrue($obj instanceof FactoryTestClass1::$class, "Failed to create class");
    }

    public function testBindClassAfter()
    {
        $factory = new \Industrial\Factory(new TestModule);

        $binder = new \Industrial\Binder($factory);
        $binder->bind(FactoryTestClass2::$class)->toSelf();
        $factory->addBound($binder);

        $obj = $factory->make(FactoryTestClass2::$class);
        $this->assertTrue($obj instanceof FactoryTestClass2::$class, "Failed to create class");
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
        $this->bind(FactoryTestClass1::$class)->toSelf();
    }
}

class FactoryTestClass1
{
    public static $class = "FactoryTestClass1";
}

class FactoryTestClass2
{
    public static $class = "FactoryTestClass2";
}
