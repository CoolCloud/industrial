<?php

require_once "test/Autoload.php";

class Industrial_Build_Action_MethodTest extends PHPUnit_Framework_TestCase
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
			Industrial\Build\Action\Method::getName(),
			Industrial\Build\Action\Method::Name
		);
	}

	public function testIsMultiple()
	{
		$this->assertTrue(Industrial\Build\Action\Method::isMultiple(), "Bind::isMultiple returned false");
	}
	
	public function testIsFinal()
	{
		$action = new Industrial\Build\Action\Method('method', array());
		$this->assertFalse($action->isFinal(), "Method::isFinal returned true");
		$action->isFinal(true);
		$this->assertTrue($action->isFinal());
	}

	public function testGetProcessor()
	{
		$action = new Industrial\Build\Action\Method("pubMethod", array('A', 'B'));
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = new MethodTest();
		$processor($this->getMockFactory(), $arg);
		$this->assertTrue(($arg instanceof MethodTest), "Processor did not maintain type MethodTest in \$obj param");
		$this->assertEquals($arg->a, 'A');
		$this->assertEquals($arg->b, 'B');
	}

	/**
 	 * @expectedException Exception
	 */
	public function testGetProcessorNonExistantMethod()
	{
		$action = new Industrial\Build\Action\Method("nonMethod", array('A', 'B'));
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = new MethodTest();
		$processor($this->getMockFactory(), $arg);
	}

	/**
 	 * @expectedException Exception
	 */
	public function testGetProcessorPrivateMethod()
	{
		$action = new Industrial\Build\Action\Method("privMethod", array('A', 'B'));
		$processor = $action->getProcessor();
		$this->assertTrue($processor instanceof Closure, "getProcessor did not return a closure");
		
		$arg = new MethodTest();
		$processor($this->getMockFactory(), $arg);
	}
}

class MethodTest
{
	public $a;
	public $b;
	public function pubMethod($a, $b)
	{
		$this->a = $a;
		$this->b = $b;
	}

	private function privMethod()
	{
	}
}
