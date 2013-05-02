<?php

require_once "test/Autoload.php";

class Industrial_Build_Action_ConstructTest extends PHPUnit_Framework_TestCase
{
	private function getMockFactory()
	{
		$c = "\\Industrial\\Factory";
		return $this->getMock($c, array(), array(
					$this->getMockForAbstractClass("\\Industrial\\Module")));
	}

	public function testGetName()
	{
		$this->assertEquals(
			Industrial\Build\Action\Construct::getName(),
			Industrial\Build\Action\Construct::Name
		);
	}

	public function testIsMultiple()
	{
		$this->assertFalse(Industrial\Build\Action\Construct::isMultiple(), "Construct::isMultiple returned true");
	}
	
	public function testIsFinal()
	{
		$action = new Industrial\Build\Action\Construct();

		$this->assertFalse($action->isFinal(), "Construct::isFinal returned true");
		$action->isFinal(true);
		$this->assertTrue($action->isFinal(), "Construct::isFinal did not change final flag");
	}

	public function testGetProcessor()
	{
		
		$action = new Industrial\Build\Action\Construct();
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = new \ReflectionClass('ConstructTestNoArg');
		$processor($this->getMockFactory(), $arg);
		$this->assertTrue(($arg instanceof ConstructTestNoArg), "Processor did create ConstructTestNoArg in \$obj param");
	}

	public function testGetProcessorWithArgs()
	{
		$action = new Industrial\Build\Action\Construct(array('a'=>'A','b'=>'B'));
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = new \ReflectionClass('ConstructTest');
		$processor($this->getMockFactory(), $arg);
		$this->assertTrue(($arg instanceof ConstructTest), "Processor did create ConstructTest in \$obj param");

		$this->assertEquals($arg->a, 'A');
		$this->assertEquals($arg->b, 'B');
	}
}

class ConstructTestNoArg
{
}

class ConstructTest
{
	public $a;
	public $b;
	public function __construct($a, $b)
	{
		$this->a = $a;
		$this->b = $b;
	}
}
