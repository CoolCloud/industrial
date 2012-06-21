Industrial Dependency Injection Framework for PHP 5.3
=====================================================
Industrial is a dependency injection framework for PHP 5.3 inspired by Google
Guice. Using the Module class configure rules for creating objects.

Example
-------
```php
<?php

class TestClass
{
    public static $class = 'TestClass';

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
        $this->bind(TestClass::$class)
             ->construct(array("one","two"))
             ->method("argument");
    }
}

$di = new \Industrial\Factory(new Module());
$testobj = $di->make(TestClass::$class);

?>
```
