<?php

include_once "src/Industrial/Factory.php";
include_once "src/Industrial/Module.php";
include_once "src/Industrial/Binder.php";

class TestClass
{
    public function __construct($one, $two)
    {
        print "Constructing TestClass:\n";
        print "First Argument: $one\n";
        print "Second Argument: $two\n";
    }

    public function method($arg)
    {
        print "Called TestClass::method()\n";
        print "Argument: $arg\n";
    }
}

class Module extends \Industrial\Module
{
    protected function config()
    {
        $this->bind('TestClass')
             ->construct(array("one","two"))
             ->method("argument");
    }
}

$di = new \Industrial\Factory(new Module());
$testobj = $di->make('TestClass');
var_dump($testobj);
