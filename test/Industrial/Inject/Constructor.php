<?php

require_once "test/Autoload.php";

class Industrial_Inject_ConstructorTest extends PHPUnit_Framework_TestCase
{
	private function getMockFactory()
	{
		$c = "\\Industrial\\Factory";
		return $this->getMock($c, array(), array(
			$this->getMockForAbstractClass("\\Industrial\\Module")));
	}

	/**
	 * @expectedException Exception
	 */
	public function testConstructError()
	{
		new \Industrial\Inject\Constructor(
			$this->getMockFactory(),
			new \ReflectionClass("TestAbstractClass")
		);
	}

	public function testConstructWithNamedArgs()
	{
		$const = new \Industrial\Inject\Constructor(
			$this->getMockFactory(),
			new \ReflectionClass("TestClass")
		);

		$args = array(
			'a' => 'A',
			'b' => 'B'
		);

		$class = $const->construct($args);
		$this->assertEqual('A', $class->a, "Did not inject argument");
		$this->assertEqual('B', $class->b, "Did not inject argument");
	}

	public function testConstuctWithNoArgs()
	{
		$const = new \Industrial\Inject\Constructor(
			$this->getMockFactory(),
			new \ReflectionClass("NoArgTestClass")
		);

		$obj = $const->construct();
		$this->assertTrue(($obj instanceof NoArgTestClass));
	}
}

abstract class TestAbstractClass
{
}

class TestClass
{
	public $a;

	public $b;

	public function __construct($a, $b)
	{
		$this->a = $a;
		$this->b = $b;
	}
}

class NoArgTestClass
{
}
