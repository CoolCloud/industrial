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

use Industrial\Build\Action;

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
     * @var \Industrial\Factory
     */
    private $_factory = null;

    /**
     * @var string
     */
    private $_className = null;

    /**
     * @var array
     */
    private $_actions = array();

    /**
     * @var \Industrial\IScope
     */
    private $_scope = null;

    /**
     * @var \Industrial\Build\Builder
     */
    private $_builder = null;

    /**
     * @var boolean
     */
    private $_finalized = false;

    /**
     * Constuctor.
     *
     * @param \Industrial\Factory $factory
     */
    public function __construct(Factory $factory) 
    {
        $this->_factory = $factory;
        $this->_scope = new Scope\Transient();
    }

    /**
     * Initial binding.
     * @param string $className
     */
    public function bind ($className)
    {
        if (!class_exists($className) && !interface_exists($className)) 
            throw new Exception("Class or Interface: "  
            . "$className does not exist or could not be found");

        $this->_className = $className;

        $this->addAction(Action::Bind($className));
        return $this;
    }

    /**
     * Binds a concrete implementation of an abstract or interface that
     * was provided during __construct()
     *
     * @param string|object $className Concrete implementation of abstract class
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function to($className)
    {
        $this->addAction(Action::To($className));
        return $this;
    }

    /**
     * Bind a concrete class to itself
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function toSelf()
    {
        $this->addAction(Action::To($this->_className));
        return $this;
    }

    /**
     * Bind a class to an instanted object, enforces Singleton Scope
     * @return \Industrial\Binder Provide fluent interface
     */
    public function toObject(\stdClass $obj)
    {
        $this->inSingletonScope();
        $action = Action::To($obj);
        $action->isFinal(true);
        $this->addAction(Action::To($obj));
        return $this;
    }

    /**
     * Provide a callback to build the bound object.
     *
     * @param callback $callback
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function using($callback)
    {
        $this->addAction(Action::Using($this->_className));
        return $this;
    }

    /**
     * Provide arguments for the __construct method of the bound class
     * @param array $args
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function construct(array $args = null)
    {
        $this->addAction(Action::Construct($args));
        return $this;
    }

    /**
     * Provide a scope object to maintain scoped injections
     * @param \Industrial\IScope Scope
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function inScope(IScope $scope)
    {
        return $this;
    }

    /**
     * Use the built in singleton scope to manage singleton objects
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function inSingletonScope()
    {
        return $this;
    }

    /**
     * Used to set a chain of methods to call during the instantiation of 
     * the bound class
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function __call($method, array $args)
    {
        $this->addAction(Action::Method($method, $args));
        /*
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
         */
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
    public function builder()
    {
        $this->finalize();
        return clone $this->_builder;
    }

    /**
     * Called by the factory during initialization 
     */
    public function finalize()
    {
        if ($this->_finalized) return;

        $builder = new Build\Builder($this->_factory);
        $priority = Action::getFirstPriority();
        do {
            if ($action = $this->getAction($priority)) {
                if (is_array($action)) {
                    foreach ($action as $act) {
                        $builder->addAction($act);
                    }
                } else {
                    $builder->addAction($action);
                }
            }
        } while ($priority = Action::getNextPriority($priority));

        // Run single build to check sanity at bind time
        // TODO This smells a little bit. Fix it.
        $builder->build();
        
        $this->_builder = $builder;
    }

    private function checkExclusive(Action\IAction $action)
    {
        array_walk ($this->_actions, function ($item) use ($action) {
            if (is_array($item))
                $item = $item[0];

            if (Action::areExclusive($item,$action)) {
                throw new Exception("Cannot use both " . $action->getName() . 
                    " and " . $action->getName());
            }
        });
    }

    private function addMultipleAction(Action\IAction $action)
    {
        if (!isset($this->_actions[$action->getName()])) {
            $this->_actions[$action->getName()] = array();
        }
        
        $this->_actions[$action->getName()][] = $action;
    }

    /**
     * @param string $priority
     */
    private function getAction($priority)
    {
        if (isset($this->_actions[$priority])) {
            return $this->_actions[$priority];
        }
    }

    /**
     * @param \Industrial\Build\Action\IAction $action
     */
    public function addAction(Action\IAction $action)
    {
        if ($this->_finalized) {
            throw new Exception("Expression has been finalized.");
        }

        $this->checkExclusive($action);

        if ($action->isMultiple()) {
            $this->addMultipleAction($action);
        } else {
            if (isset($this->_actions[$action->getName()])) {
                throw new Exception("Cannot add multiple actions of type " . $action->getName());
            } else {
                $this->_actions[$action->getName()] = $action;
            }
        }

        if ($action->isFinal()) {
            $this->_finalized = true;
        }
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
