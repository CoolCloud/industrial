<?php 

require_once "test/Autoload.php";

class Industrial_Build_ActionTest extends PHPUnit_Framework_TestCase
{
    public function callStaticTest()
    {
        $action = Action::Bind("ActionTestA");
        $this->assertTrue($action instanceof \Industrial\Build\Action\Bind);
    }

    /**
     * @expectedException \Industrial\Build\Action\Exception
     */
    public function callStaticTestThrow()
    {
        $action = Action::Blind("ActionTestA");
    }
}

class ActionTestA {}
