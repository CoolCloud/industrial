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
 * @version 0.4.0
 * @since 0.4
 */
namespace Industrial\Inject;

/**
 * Lazy Constructor
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.4.0
 * @since 0.4
 */
class LazyConstructor implements IConstructor
{
	private $_factory;

	private $_class;

	private $_name;

	/**
	 * @param \Industrial\Factory $factory
	 */
	public function __construct(\Industrial\Factory $factory, $class, $name = null)
	{
		$this->_factory = $factory;
		$this->_class = $class;
		$this->_name = $name;
	}

	/**
	 * Construct an object by injecting available parameters
	 */
	public function construct()
	{
		return $this->_factory->make($this->_class, $this->_name);
	}
}


