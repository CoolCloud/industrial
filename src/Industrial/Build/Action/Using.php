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
 * Using Action
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.2.0
 * @since 0.2
 */
class Using implements IAction
{
	const Name = "Using";

	private $_final;

	private $_callback;

	public static function getName()
	{
		return self::Name;
	}

	public function __construct ($callback)
	{
		if (!is_callable($callback))
			throw new \Exception("Using must be provided with a callable function");
		$this->_callback = $callback;
	}

	public static function isMultiple()
	{
		return false;
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
		$cb = $this->_callback;
		return function ($factory, \ReflectionClass &$obj) use ($cb) {
			$refl = $obj;
			$obj = $cb();
			if (!$refl->isInstance($obj))
				throw new \Exception("Using callback must provide an instance of " . $refl->name);
		};
	}
}

