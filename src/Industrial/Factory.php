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

use Industrial\Inject;

/**
 * Dependency Injection factory
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.1.1
 * @since 0.1
 */
class Factory
{
	/**
	 * Array of \Industrial\Binder
	 * @var array
	 */
	private $_bound = array();

	/**
	 * @var array
	 */
	private $_modules = array();

	/**
	 * @var boolean
	 */
	private $_configured = false;

	/**
	 * @var array
	 */
	private $_params = array();

	/**
	 * Constructor
	 * @param \Industrial\Module
	 */
	public function __construct()
	{
		$modules = func_get_args();
		foreach ($modules as $module) {
			if (!($module instanceof \Industrial\Module)) 
				throw new Exception("Modules must be of type: \\Industrial\\Module");

			$this->_modules[] = $module;
		}
	}

	/**
	 *
	 */
	public function addModule(\Industrial\Module $module)
	{
		if ($this->_configured)
			throw new Exception("Factory already configured");

		$this->_modules[] = $module;
	}

	/**
	 * Add a binder to the factory.
	 * @param \Industrial\Binder
	 */
	public function addBound(Binder $bound)
	{
		$this->_bound[] = $bound;
	}

	/**
	 * Get the binder for the given class name.
	 * @param string $className
	 * @return \Industrial\Binder
	 */
	public function getBound($className, $name = null) 
	{
		$this->configure();
		foreach ($this->_bound as $bound) {
			if ($bound->is($className, $name)) 
				return $bound;
		}
	}

	/**
	 * Add parameters to be passed to constructor on build
	 * @param string|array $param
	 * @param mixed $value
	 * @return \Industrial\Factory Provide a fluent interface
	 * @since 0.3
	 */
	public function with($param, $value = null)
	{
		if (is_array($param)) {
			$this->_params = array_merge($this->_params, $param);
		} else {
			$this->_params[$param] = $value;
		}

		return $this;
	}

	/**
	 * Instantiate a class according to rules in it's binder.
	 * @param string $class
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function make($class, $name = null)
	{
		$this->configure();

		if ($binder = $this->getBound($class, $name)) {
			$obj =  $binder->builder()->build($this->_params);
			$this->_params = array();
			return $obj;
		} else { 
			try {
				$binder = new Binder($this);
				$binder->bind($class)->toSelf();
				$this->addBound($binder);

				$obj = $binder->builder()->build($this->_params);
				$this->_params = array();
				return $obj;
			} catch (Exception $e) {
				throw new \Exception("JIT Binding failed for: $class ", 0, $e);
			}
		}

		throw new \Exception("Class: $class has not been bound");

	}

	/**
	 *
	 */
	public function lazy($class, $name = null)
	{
		return new Inject\LazyConstructor($this, $class, $name);
	}

	private function configure()
	{
		if ($this->_configured) return;
		foreach ($this->_modules as $module) {
			$module->configure($this);
		}
		$this->_configured = true;
	}
}
