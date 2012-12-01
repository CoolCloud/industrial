<?php

require_once "test/Autoload.php";

class Industrial_BinderTest extends PHPUnit_Framework_TestCase
{
    private function getMockFactory()
    {
        $c = "\\Industrial\\Factory";
        return $this->getMock($c, array(), array(
            $this->getMockForAbstractClass("\\Industrial\\Module")));
    }

    public function testBindClass()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind(BinderTestClass1::$class);
        $this->assertTrue($binder->is(BinderTestClass1::$class), "Failed to match Binder classname");
    }

    public function testBuildClass()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind(BinderTestClass1::$class)->toSelf();
        $obj = $binder->builder()->build();
        $this->assertTrue($obj instanceof BinderTestClass1::$class, "Failed to build proper class");
    }

    public function testConstuctorArguments()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind(BinderTestClass1::$class);
        $a1 = rand(0,10000);
        $a2 = rand(0,10000);
        $binder->construct(array($a1,$a2));
        $obj = $binder->builder()->build();
        $this->assertTrue(($obj->constructor_argument1 == $a1 && $obj->constructor_argument2 == $a2), "Failed to set constructor arguments");
    }

    public function testTypedConstructorArguments1()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind(BinderTestClass2::$class);
        $binder->construct(array(new BinderTestClass1));
        $obj = $binder->builder()->build();
        $this->assertTrue(($obj instanceof BinderTestClass2), "Failed to build class");
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testTypedConstructorArguments2()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind(BinderTestClass2::$class);
        $binder->construct(array("String"));
    }

    public function testTypedConstructerArguments3()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind(BinderTestClass3::$class);
        $binder->construct(array(array()));
        $obj = $binder->builder()->build();
        $this->assertTrue(($obj instanceof BinderTestClass3), "Failed to build class");
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testTypedConstructorArguments4()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind(BinderTestClass3::$class);
        $binder->construct(array("String"));
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testCountedConstructorArguments()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind(BinderTestClass3::$class);
        $binder->construct(array());
    }

    public function testMethodArguments()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind(BinderTestClass1::$class);
        $binder->toSelf();
        $a1 = rand(0,10000);
        $a2 = rand(0,10000);
        $a3 = rand(0,10000);
        $a4 = rand(0,10000);
        $binder->method1($a1,$a2);
        $binder->method2($a3,$a4);
        $obj = $binder->builder()->build();
        $this->assertTrue(($obj->method1_argument1 == $a1 && $obj->method1_argument2 == $a2 && $obj->method2_argument1 == $a3 && $obj->method2_argument2 == $a4), "Failed to set method arguments");
    }
    
    public function testInterfaceBinding()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind("BinderTestInterface1");
    	$binder->to(BinderTestClass4::$class)->method1();
        $obj = $binder->builder()->build();

        $this->assertTrue($obj instanceof BinderTestInterface1, "Object not instance of interface");
        $this->assertTrue($obj instanceof BinderTestClass4, "Object not instance of concrete class");
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInterfaceBindingWithoutImplementedInterface()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind("BinderTestInterface1");
    	$binder->to(BinderTestClass3::$class);
    	$binder->builder()->build();
    }
    
    public function testAbstractBinding()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind(BinderTestAbstract1::$class);
    	$binder->to(BinderTestClass5::$class)->method1();
        $obj = $binder->builder()->build();

        $this->assertTrue($obj instanceof BinderTestAbstract1, "Not instance of abstract class");
        $this->assertTrue($obj instanceof BinderTestClass5, "Not instance of concrete class");
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testAbstractBindingWithoutExtendedClass()
    {
        $binder = new \Industrial\Binder($this->getMockFactory());
        $binder->bind(BinderTestAbstract1::$class);
    	$binder->to(BinderTestClass3::$class);
    	$binder->builder()->build();
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testBuildAbstractBoundClass()
    {
        $binder = \Industrial\Binder::bind($this->getMockFactory());
        $binder->bind(BinderTestAbstract1::$class)->toSelf();
        $binder->builder()->build();
    } 
}

class BinderTestClass1
{
    public static $class = "BinderTestClass1";

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

class BinderTestClass2
{
    public static $class = "BinderTestClass2";
    public function __construct(BinderTestClass1 $typed_argument) {}
}

class BinderTestClass3
{
    public static $class = "BinderTestClass3";
    public function __construct(array $array_argument) {}
}

interface BinderTestInterface1
{
	public function method1();
}

class BinderTestClass4 implements BinderTestInterface1
{
	public static $class = "BinderTestClass4";
	
	public function method1()
	{
		
	}
}

abstract class BinderTestAbstract1
{
	public static $class = "BinderTestAbstract1";
	abstract public function method1();
}

class BinderTestClass5 extends BinderTestAbstract1
{
	public static $class = "BinderTestClass5";
	
	public function method1()
	{
		
	}
}
