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
 * @version 0.2.0
 * @since 0.2
 */
namespace Industrial\Inject;

/**
 * Constructor
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.2.0
 * @since 0.2
 */
class Constructor
{
	private $_factory;

	private $_reflection;

	/**
	 * @param \Industrial\Factory $factory
	 */
	public function __construct(\Industrial\Factory $factory, \ReflectionClass $refl)
	{
		$this->_factory = $factory;
		$this->_reflection = $refl;

		if ($this->_reflection->isAbstract() || $this->_reflection->isInterface())
			throw new Exception ("Cannot construct");
	}

	/**
	 * Construct an object by injecting available parameters
	 * @param \ReflectionClass $refl
	 */
	public function construct(array $args = null)
	{
		if ($constr = $this->_reflection->getConstructor()) {
			$params = $constr->getParameters();
			$inj_params = $this->getConstructorArguments($params, $args);
			return $this->_reflection->newInstanceArgs($inj_params);
		}

		return $this->_reflection->newInstanceWithoutConstructor();
	}

	private function getConstructorArguments ($params, array $args = null)
	{
		if ($args === null) $args = array();
		$inj_params = array();

		foreach ($params as $param) {
			if (array_key_exists($param->getName(), $args)) {
				$inj_params[] = $args[$param->getName()];
				continue;
			}

			if (null === ($pc = $param->getClass())) {
				if ($param->isDefaultValueAvailable()) {
					$inj_params[] = $param->getDefaultValue();
					continue;
				}
			} else {
				$inj_params[] = $this->getInjectableClass($pc->name);
				continue;
			}

			throw new Exception("Could not inject parameter: " . $param->getName() . " in class: " . $this->_reflection->name . "\n");
		}

		return $inj_params;
	}

	private function getInjectableClass($class)
	{
		try {
			return $this->_factory->make($class);
		} catch (\Exception $e) {
			throw new Exception("Caught Exception while injecting constructor parameter", 0, $e);
		}
	}
}


