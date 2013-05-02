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
namespace Industrial\Build\Action;

use Industrial\Build;
use Industrial\Reflect;
use Industrial\Inject;

/**
 * Construct Action
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.2.0
 * @since 0.2
 */
class Construct implements IAction
{
	const Name = "Construct";

	private $_final = false;

	private $_args;

	public static function getName()
	{
		return self::Name;
	}

	public static function isMultiple()
	{
		return false;
	}

	public function __construct(array $args = null)
	{
		$this->_args = $args;
	}

	public function isFinal($final = null)
	{
		if (null !== $final) {
			$this->_final = $final;
		}
		return $this->_final;
	}

	public function getProcessor()
	{
		$args = $this->_args;
		return function ($factory, \ReflectionClass &$obj) use ($args) {
			if ($args === null) {
				$obj = $obj->newInstance(); 
			} else {
				$obj = $obj->newInstanceArgs($args);
			}
		};
	}
}

