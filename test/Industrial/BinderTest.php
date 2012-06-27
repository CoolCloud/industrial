<?php

require_once "src/Industrial/Binder.php";

class Industrial_BinderTest extends PHPUnit_Framework_TestCase
{
    private static $classname = "TestClass";

    public function testBindClass()
    {
        $binder = new \Industrial\Binder(self::$classname);
        $this->assertTrue($binder->is(self::$classname), "Failed to match Binder classname");
    }

    public function testBuildClass()
    {
        $binder = new \Industrial\Binder(self::$classname);
        $obj = $binder->build();
        $this->assertTrue($obj instanceof self::$classname, "Failed to build proper class");
    }

    public function testConstuctorArguments()
    {
        $binder = new \Industrial\Binder(self::$classname);
        $a1 = rand(0,10000);
        $a2 = rand(0,10000);
        $binder->construct(array($a1,$a2));
        $obj = $binder->build();
        $this->assertTrue(($obj->constructor_argument1 == $a1 && $obj->constructor_argument2 == $a2), "Failed to set constructor arguments");
    }

    public function testMethodArguments()
    {
        $binder = new \Industrial\Binder(self::$classname);
        $a1 = rand(0,10000);
        $a2 = rand(0,10000);
        $a3 = rand(0,10000);
        $a4 = rand(0,10000);
        $binder->method1($a1,$a2);
        $binder->method2($a3,$a4);
        $obj = $binder->build();
        $this->assertTrue(($obj->method1_argument1 == $a1 && $obj->method1_argument2 == $a2 && $obj->method2_argument1 == $a3 && $obj->method2_argument2 == $a4), "Failed to set method arguments");
    }
}

class TestClass
{
    public $constructor_argument1;
    public $constructor_argument2;
    public $method1_argument1;
    public $method1_argument2;
    public $method2_argument1;
    public $method2_argument2;

    public function __construct($argument1 = null, $argument2 = null)
    {
        $this->constructor_argument1 = $argument1;
        $this->constructor_argument2 = $argument2;
    }

    public function method1($argument1 = null,$argument2 = null)
    {
        $this->method1_argument1 = $argument1;
        $this->method1_argument2 = $argument2;
    }

    public function method2($argument1 = null,$argument2 = null)
    {
        $this->method2_argument1 = $argument1;
        $this->method2_argument2 = $argument2;
    }
}
