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
namespace Industrial\Build;

use \Industrial\Reflect;

/**
 * Action
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.2.0
 * @since 0.1
 */
class Action
{
	const Before = 0;
	const After = 1;
	const Last = 2;

	private static $_actionNamespace = "\\Industrial\\Build\\Action\\";

	private static $_priority;
	private static $_exclusive;

	private static function _init()
	{
		static $init = false;

		if ($init) return;

		$init = true;
		self::$_priority = array(
				Action\Bind::getName() => self::$_actionNamespace . "Bind",
				Action\To::getName() => self::$_actionNamespace . "To",
				Action\Construct::getName() => self::$_actionNamespace . "Construct",
				Action\Using::getName() => self::$_actionNamespace . "Using",
				Action\Method::getName() => self::$_actionNamespace . "Method"
				);

		self::$_exclusive = array(
				array(
					Action\To::getName(),
					Action\Construct::getName()),
				array(
					Action\To::getName(),
					Action\Using::getName()),
				array(
					Action\Construct::getName(),
					Action\Using::getName())
				);
	}

	/** TODO: Enable custom actions * /
	  private static function add($action, $place = Last, $reference = "")
	  {
	  static $actionClass = "\\Industrial\\Build\\Action\\IAction";

	  $refl = Reflect\Cache::get($action);

	  if (!$refl->isSubclassOf($actionClass))
	  throw new \Exception("Added action must extend " . $actionClass);

	  if ($place == self::Last) {
	  self::$_priority[] = $action;
	  } else {
	  if (!$reference) 
	  throw new \Exception("Must include a placement reference");

	  $idx = array_search($reference, $this->_priority);
	  if ($idx === false) 
	  throw new \Exception("No priority for placement reference: " . $reference);

	  if ($place == self::After) $idx++;

	  if ($idx < 2)
	  throw new \Exception("Cannot set a priority before: " . self::To);

	  array_splice(self::$_priority, $idx, 0, $action);
	  } 
	  }
	/** */

	public static function __callStatic ($method, $args)
	{
		self::_init();
		$method = ucfirst($method);
		if (array_key_exists($method, self::$_priority)) {
			$class = self::$_priority[$method];
			$refl = Reflect\Cache::get($class);

			return $refl->newInstanceArgs($args);
		}

		throw new \Exception("Action: " . $method . " does not exists.");
	}

	public static function getFirstPriority ()
	{
		self::_init();
		$keys = array_keys(self::$_priority);
		return $keys[0];
	}

	/**
	 * 
	 */
	public static function getNextPriority ($priority)
	{
		self::_init();
		$keys = array_keys(self::$_priority);
		if (false === ($idx = array_search($priority, $keys)))
			throw new \Exception("Priority " . $priority . " does not exist");

		$idx++;
		if (count($keys) > $idx)
			return $keys[$idx];

		return false;
	}

	/**
	 * Check if two actions may not be used together.
	 * @param \Industrial\Build\Action\IAction $a
	 * @param \Industrial\Build\Action\IAction $b
	 * return boolean
	 */
	public static function areExclusive (Action\IAction $a, Action\IAction $b)
	{
		self::_init();
		$a = get_class($a);
		$b = get_class($b);

		foreach (self::$_exclusive as $exclusive) 
		{
			if (false !== array_search($a, $exclusive) && 
					false !== array_search($b, $exclusive))

				return true;
		}

		return false;
	}
}
