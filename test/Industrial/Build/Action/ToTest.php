<?php

require_once "test/Autoload.php";

class Industrial_Build_Action_ToTest extends PHPUnit_Framework_TestCase
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
			Industrial\Build\Action\To::getName(),
			Industrial\Build\Action\To::Name
		);
	}

	public function testIsMultiple()
	{
		$this->assertFalse(Industrial\Build\Action\To::isMultiple(), "Bind::isMultiple returned true");
	}
	
	public function testIsFinal()
	{
		$action = new Industrial\Build\Action\To("ToTest");
		$this->assertFalse($action->isFinal(), "To::isFinal returned true");
		$action->isFinal(true);
		$this->assertTrue($action->isFinal());
	}

	public function testGetProcessor()
	{
		$action = new Industrial\Build\Action\To("ToTest");
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = new \ReflectionClass("ToTest");
		$processor($this->getMockFactory(), $arg);
		$this->assertTrue(($arg instanceof ToTest), "Processor did create reflection in \$obj param");
	}
	
	public function testGetProcessorWithAbstractClass()
	{
		$action = new Industrial\Build\Action\To("ToTest");
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = new \ReflectionClass("AbstractToTest");
		$processor($this->getMockFactory(), $arg);
		$this->assertTrue(($arg instanceof ToTest), "Processor did create reflection in \$obj param");
	}

	public function testGetProcessorWithInterface()
	{
		$action = new Industrial\Build\Action\To("ToTest");
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = new \ReflectionClass("InterfaceToTest");
		$processor($this->getMockFactory(), $arg);
		$this->assertTrue(($arg instanceof ToTest), "Processor did create reflection in \$obj param");
	}

	/**
	 * @expectedException Exception
	 */
	public function testGetProcessorWithIncorrectAbstract()
	{
		$action = new Industrial\Build\Action\To("ToTest");
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = new \ReflectionClass("AnotherAbstractToTest");
		$processor($this->getMockFactory(), $arg);
		$this->assertTrue(($arg instanceof ToTest), "Processor did create reflection in \$obj param");
	}
}

class ToTest extends AbstractToTest implements InterfaceToTest
{
}

class AbstractToTest
{
}

class AnotherAbstractToTest
{
}

interface InterfaceToTest
{
}
