<?php 

use \Industrial\Build\Action;

require_once "test/Autoload.php";

class Industrial_Build_ActionTest extends PHPUnit_Framework_TestCase
{
    public function testCallStatic()
    {
        $action = Action::Bind("ActionTestA");
        $this->assertTrue($action instanceof \Industrial\Build\Action\Bind);
    }

    /**
     * @expectedException Exception
     */
    public function testCallStaticThrow()
    {
        $action = Action::Blind("ActionTestA");
    }
}

class ActionTestA {}
