Industrial Dependency Injection Framework for PHP 5.3
=====================================================
Industrial is a dependency injection framework for PHP 5.3 inspired by Google 
Guice. 

Getting Started
---------------
The recommended way to use Industrial is through [composer](http://getcomposer.org). 
Add Industrial to your project's ``composer.json`` file:
    
    {
        "require": {
            "pihi/industrial": "*"
        }
    }

Find out more about Composer installation and use, along with other best
practices at http://getcomposer.org/doc/00-intro.md


Basic Usage
-----------
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
        $this->bind(TestClass::$class)          // Create an \Industrial\Binder for \TestClass
             ->construct(array("one","two"))    // Apply these parameters to the constructor
             ->method("argument");              // After __construct() call method("argument")

        $this->bind(TestClass::$class)          // Create an \Industrial\Binder for \TestClass
             ->using(function(){                // Use a callback to instantiate object
                    return new TestClass();     //
               })->method("argument");          // Call method("argument") 

        $this->bind(TestClass::$class)          // Accomplishes the same as above
             ->using(function(){                //
                    $obj = new TestClass();     //
                    $obj->method("argument");   //
                    return $obj;                //
               });                              //
        
        $this->bind(TestClass::$class)          // Accomplishes the same as above
             ->using(array(                     // 
                $this,'initTestClass'));        //
    }

    public function initTestClass() {
        $obj = new TestClass();
        $obj->method("argument");
        return $obj;
    }
}

$di = new \Industrial\Factory(new Module());
$testobj = $di->make(TestClass::$class);

?>
```

