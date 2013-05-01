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
     * @expectedException Exception
     */
    public function testNoClass()
    {
        $factory = new \Industrial\Factory(new TestModule);
        $factory->make("NotAClass");
    }

    public function testJustInTimeBinding()
    {
        $factory = new \Industrial\Factory(new TestModule);

        $jit = $factory->make(JITBinder::$class);

        $this->assertTrue($jit instanceof JITBinder);
        $this->assertTrue($jit->argument instanceof JITArgument);
    }

    public function testNamedBinding()
    {
        $factory = new \Industrial\Factory(new TestModule);

        $n1 = $factory->make(NamedBinder::$class, "n1");
        $n2 = $factory->make(NamedBinder::$class, "n2");

        $this->assertEquals("n1",$n1->name);
        $this->assertEquals("n2",$n2->name);
    }

    public function testJITBuildTimeParameter()
    {
        $factory = new \Industrial\Factory(new TestModule);

        $jit = $factory->with('arg2', 'value')->make(JITParamBinder::$class);
        $this->assertTrue($jit->arg1 instanceof JITArgument);
        $this->assertEquals($jit->arg2, 'value');
        $this->assertEquals($jit->arg3, 'default');

        $jit = $factory->with(array('arg2'=>'value', 'arg3'=>'value2'))->make(JITParamBinder::$class);
        $this->assertTrue($jit->arg1 instanceof JITArgument);
        $this->assertEquals($jit->arg2, 'value');
        $this->assertEquals($jit->arg3, 'value2');
    }

    public function testMultiModuleFactory()
    {
        $factory = new \Industrial\Factory(
            new TestModule, new TestModuleTwo);

        $obj = $factory->make(NamedBinder::$class, "n3");
        $this->assertTrue($obj instanceof NamedBinder);
        $this->assertEquals("n3",$obj->name);
    }

	public function testLazy()
	{
		$factory = new \Industrial\Factory(
			new \TestModuleThree
		);

		$lazyclass = $factory->make(LazyBound::$class);
		$this->assertTrue($lazyclass instanceof LazyBound);
		$this->assertTrue($lazyclass->lazy instanceof LazyArg);
	}
}

class TestModule extends \Industrial\Module
{
    protected function config()
    {
        $this->bind(FactoryTestClass1::$class)->toSelf();
        $this->bind(JITBinder::$class)->toSelf();
        $this->bind(JITArgument::$class)->toSelf();
        $this->bind(NamedBinder::$class)->named("n1")
            ->toSelf()->method("setName",array("n1"));
        $this->bind(NamedBinder::$class)->named("n2")
            ->toSelf()->method("setName",array("n2"));
        $this->bind(JITParamBinder::$class)->toSelf();
    }
}

class TestModuleTwo extends \Industrial\Module
{
    protected function config()
    {
        $this->bind(NamedBinder::$class)->named("n3")
            ->toSelf()->method("setName", array("n3"));
    }
}

class TestModuleThree extends \Industrial\Module
{
	protected function config()
	{
		$this->bind(LazyBound::$class)
			->toSelf()
			->with('lazy', $this->lazy(LazyArg::$class));
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

class JITBinder 
{
    public static $class = "JITBinder";

    public $argument;

    public function __construct(JITArgument $argument)
    {
        $this->argument = $argument;
    }
}

class JITArgument
{
    public static $class = "JITArgument";
}

class JITParamBinder
{
    public static $class = "JITParamBinder";

    public $arg1;

    public $arg2;

    public $arg3;

    public function __construct(JITArgument $arg1, $arg2, $arg3 = 'default')
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
        $this->arg3 = $arg3;
    }
}

class NamedBinder
{
    public static $class = "NamedBinder";

    public $name;

    public function setName($name) 
    {
        $this->name = $name;
    }
}

class LazyBound
{
	public static $class = "LazyBound";
	public $lazy;
	public function __construct($lazy)
	{
		$this->lazy = $lazy;
	}
}

class LazyArg
{
	public static $class = "LazyArg";
}
