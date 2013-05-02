<?php

require_once "test/Autoload.php";

class Industrial_Build_Action_BindTest extends PHPUnit_Framework_TestCase
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
			Industrial\Build\Action\Bind::getName(),
			Industrial\Build\Action\Bind::Name
		);
	}

	public function testIsMultiple()
	{
		$this->assertFalse(Industrial\Build\Action\Bind::isMultiple(), "Bind::isMultiple returned true");
	}
	
	public function testIsFinal()
	{
		$action = new Industrial\Build\Action\Bind("BindTest");
		$this->assertFalse($action->isFinal(), "Bind::isFinal returned true");
	}

	public function testGetProcessor()
	{
		$action = new Industrial\Build\Action\Bind("BindTest");
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = null;
		$processor($this->getMockFactory(), $arg);
		$this->assertTrue(($arg instanceof ReflectionClass), "Processor did create reflection in \$obj param");
		$class = $arg->getName();
		$this->assertTrue($class == 'BindTest', "Reflection: $class was not expected: BindTest");
	}

	/**
 	 * @expectedException Exception
	 */
	public function testNonNullProcessorObject()
	{
		$action = new Industrial\Build\Action\Bind("BindTest");
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure);
		$arg = new stdclass;
		$obj = $processor($this->getMockFactory(), $arg);
	}
}

class BindTest
{
}
