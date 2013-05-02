<?php

require_once "test/Autoload.php";

class Industrial_Build_Action_UsingTest extends PHPUnit_Framework_TestCase
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
			Industrial\Build\Action\Using::getName(),
			Industrial\Build\Action\Using::Name
		);
	}

	public function testIsMultiple()
	{
		$this->assertFalse(Industrial\Build\Action\Using::isMultiple(), "Bind::isMultiple returned true");
	}
	
	public function testIsFinal()
	{
		$action = new Industrial\Build\Action\Using(function(){});
		$this->assertFalse($action->isFinal(), "Using::isFinal returned true");
		$action->isFinal(true);
		$this->assertTrue($action->isFinal());
	}

	public function testGetProcessor()
	{
		$action = new Industrial\Build\Action\Using(
			function(){
				return new UsingTest;
			}
		);
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = new \ReflectionClass("UsingTest");
		$processor($this->getMockFactory(), $arg);
		$this->assertTrue(($arg instanceof UsingTest), "Processor did create reflection in \$obj param");
	}

	/**
 	 * @expectedException Exception
	 */
	public function testGetProcessorWrongType()
	{
		$action = new Industrial\Build\Action\Using(
			function(){
				return new stdclass;
			}
		);
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = new \ReflectionClass("UsingTest");
		$processor($this->getMockFactory(), $arg);
	}
}

class UsingTest
{
}
