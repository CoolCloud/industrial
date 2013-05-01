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
	 * @var string
	 */
	private $_name = null;

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
		$this->_builder = new Build\Builder($this->_factory);
	}

	/**
	 * Initial binding.
	 * @param string $className
	 * @param string $name
	 * @return \Industrial\Binder Provide fluent interface
	 */
	public function bind ($className, $name = null)
	{
		if (!class_exists($className) && !interface_exists($className)) 
			throw new Exception("Class or Interface: "  
					. "$className does not exist or could not be found");

		$this->_className = $className;

		$this->addAction(Action::Bind($className));
		$this->_name = $name;
		return $this;
	}

	/**
	 * Name this binding
	 * @param string $name
	 * @return \Industrial\Binder Provide fluent interface
	 */
	public function named ($name)
	{
		$this->_name = $name;
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
	 * Bind a parameter for use in injection
	 * 
	 */
	public function with($param, $value = null)
	{
		if (is_array($param)) 
		{
			$this->_builder->addParams($param);
		} 
		else
		{
			$this->_builder->addParam($param, $value);
		}
	}

	/**
	 * Provide a callback to build the bound object.
	 *
	 * @param callback $callback
	 * @return \Industrial\Binder Provide Fluent Interface
	 */
	public function using($callback)
	{
		$this->addAction(Action::Using($callback));
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

	public function method($method, array $args = null)
	{
		return $this->__call($method, $args);
	}

	/**
	 * Used to set a chain of methods to call during the instantiation of 
	 * the bound class
	 * @return \Industrial\Binder Provide Fluent Interface
	 */
	public function __call($method, array $args)
	{
		$this->addAction(Action::Method($method, $args));
		return $this;
	}

	/** 
	 * Check if the bound class is the given class
	 * @param string $class
	 * @param string $name
	 * @return boolean
	 */
	public function is($class, $name = null)
	{
		if (is_object($class)) 
			return ($class instanceof $this->_className && $this->_name == $name);

		return ($this->_className == $class && $this->_name == $name);
	}

	/**
	 * Instantiate and initialize the bound class according to the given rules
	 * @return 
	 */
	public function builder()
	{
		$this->finalize();
		$builder = clone $this->_builder;
		return $builder;
	}

	/**
	 * Called by the factory during initialization 
	 */
	public function finalize()
	{
		if ($this->_finalized) return;

		$priority = Action::getFirstPriority();
		do {
			if ($action = $this->getAction($priority)) {
				if (is_array($action)) {
					foreach ($action as $act) {
						$this->_builder->addAction($act);
					}
				} else {
					$this->_builder->addAction($action);
				}
			}
		} while ($priority = Action::getNextPriority($priority));

		$this->_finalized = true;
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
			$this->finalize();
		}
	}
}
