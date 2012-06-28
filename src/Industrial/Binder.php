<?php
/**
 * Industrial Dependency Injection Framework
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.1.2
 * @since 0.1
 */
namespace Industrial;

/**
 * Binder.
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.1.2
 * @since 0.1
 */
class Binder
{
    /**
     * @var string
     */
    private $_className = null;

    /**
     * @var \ReflectionClass
     */
    private $_abstReflection = null;

    /**
     * @var \ReflectionClass
     */
    private $_implReflection = null;

    /**
     * @var callback
     */
    private $_callback = null;

    /** 
     * @var array
     */
    private $_constructArgs = null;

    /**
     * @var array
     */
    private $_methods = array();

    /**
     * Static access to the constructor for chaining.
     *
     * @param string $className
     */
    public static function bind($className) 
    {
        return new self($className);
    }

    /**
     * Constuctor.
     *
     * @param string $className
     */
    public function __construct($className) 
    {
        if (!class_exists($className) && !interface_exists($className)) 
            throw new \InvalidArgumentException("Class or Interface: "  
            . "$className does not exist or could not be found");

        $this->_className = $className;

        $reflection = new \ReflectionClass($className);
        if ($reflection->isAbstract() || $reflection->isInterface()) {
            $this->_abstReflection = $reflection;
        } else {
            $this->_implReflection = $reflection;
        }
    }

    /**
     * Binds a concrete implementation of an abstract or interface that
     * was provided during __construct()
     *
     * @param string $className Concrete implementation of abstract class
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function to($className)
    {
        if ($this->_implReflection && !$this->_abstReflection)
            $this->_abstReflection = $this->_implReflection;

        $this->_implReflection = new \ReflectionClass($className);

        if (!($this->_implReflection->isSubclassOf(
                $this->_abstReflection->name))) 
            throw new \InvalidArgumentException(sprintf("%s does not extend " 
                . "or implement %s", $className, $this->_abstReflection->name));
        
        return $this;
    }

    /**
     * Provide a callback to build the bound object. If constructor arguments
     * are also provided the constructor will be called first, then passed to 
     * the callback. 
     *
     * @param callback $callback
     * @param boolean $constructFirst Force calling empty constructor if no 
     *  constructor arguments are provided
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function using($callback, $constructFirst = false)
    {
        if ($this->_constructorArgs) 
            throw new \BadMethodCallException("Only one of construct() or "
                . "using() may be used.");

        if (!is_callable($callback))
            throw new \BadMethodCallException("Callback must be a callable "
                . "function");

        $this->_callback = $callback;
        return $this;
    }

    /**
     * Provide arguments for the __construct method of the bound class
     * @param array $args
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function construct(array $args = null)
    {
        if ($this->_callback) 
            throw new \BadMethodCallException("Only one of construct() or "
                . "using() may be used.");

        $this->checkArguments($this->_implReflection->getConstructor(), $args);
        $this->_constructArgs = $args;
        return $this;
    }

    /**
     * Used to set a chain of methods to call during the instantiation of 
     * the bound class
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function __call($method, array $args)
    {
        try {
            $refl_method = $this->_implReflection->getMethod($method);
            if (!$refl_method->isPublic())
                throw new \InvalidArgumentException(sprintf("%s::%s() is not "
                    . "publicly visible", $this->_className, $method));
            $this->checkArguments($refl_method, $args);
        } catch (\ReflectionException $e) {
            if (!$this->_implReflection->hasMethod("__call")) 
                throw new \InvalidArgumentException(sprintf("%s::%s() does not "
                    . "exist or cannot be called",$this->_className, $method));

            $args = array($method, $args);
            $method = "__call";
        }

        $this->_methods[] = array($method,$args);
        return $this;
    }

    /** 
     * Check if the bound class is the given class
     * @param string $class
     * @return boolean
     */
    public function is($class)
    {
        if (is_object($class)) return ($class instanceof $this->_className);
        return ($this->_className == $class);
    }

    /**
     * Instantiate and initialize the bound class according to the given rules
     * @return 
     */
    public function build()
    {
        if (!$this->_implReflection) 
            throw new \BadMethodCallException(sprintf("No implementation "
                . "of abstract class or interface %s could be found",
                $this->_abstReflection->name));

        if ($this->_callback) {
            $obj = $this->_callback();
            if (!($obj instanceof $this->__className)) 
                throw new \InvalidArgumentException(sprintf("Callback must "
                    . "provide an instance of %s", $this->_className));
        }

        if ($this->_constructArgs) {
            $obj = $this->_implReflection->newInstanceArgs($this->_constructArgs);
        } else {
            $obj = $this->_implReflection->newInstance();
        }

        foreach ($this->_methods as $method) {
            call_user_func_array(array($obj,$method[0]),$method[1]);
        }

        return $obj;
    }

    /**
     *  
     */
    private function checkArguments(\ReflectionMethod $method, array $args)
    {
        $cnt = count($args);
        if ($cnt < $method->getNumberOfRequiredParameters()) {
            throw new \BadMethodCallException(sprintf("%s::%s() requires %d "
                . "arguments, %d were passed", $this->_className, $method->name, 
                $method->getNumberOfRequiredParameters(), $cnt));
        }

        $c = 0;
        foreach ($method->getParameters() as $param) {
            $arg = $args[$c];
            $this->checkArgument($param, $arg, $method->name);
            if (++$c >= $cnt) break;
        }
    }

    /**
     */
    private function checkArgument(\ReflectionParameter $param, $arg, $method)
    {
        if (is_null($arg) && !$param->allowsNull()) 
            throw new \BadMethodCallException(sprintf("%s::%s() does not "
                . "allow null arguments in position %s", $this->_className, 
                $method, $param->getPosition()));
        if ($param->isArray()) {
            if (!is_array($arg)) 
                throw new \BadMethodCallException(sprintf("%s::%s() requires "
                    . "argument in position %s to be an array",$this->_className, 
                    $method, $param->getPosition()));
        } else if ($param->getClass()) {
            $cname = $param->getClass()->getName();
            if (!($arg instanceof $cname))
                throw new \BadMethodCallException(sprintf("%s::%s() requires "
                    . "argument in position %s to be an instance of %s", 
                    $this->_className, $method, $param->getPosition(),
                    $param->getClass()->getName()));
        }
    }
}
