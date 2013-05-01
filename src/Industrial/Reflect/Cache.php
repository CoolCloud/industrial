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
 * @version 0.2
 * @since 0.2
 */
namespace Industrial\Reflect;

/**
 * ReflectionCache
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.2.0
 * @since 0.2
 */
class Cache
{
	/**
	 * @var array
	 */
	private static $_cache = array();

	/**
	 * Get number of cached classes
	 * @return int
	 */
	public static function size()
	{
		return count(self::$_cache);
	}

	/**
	 *
	 */
	public static function debug()
	{
		var_dump(self::$_cache);
	}

	/**
	 * Find and return a cached copy 
	 * If no cached copy exists cache on
	 * @param string $class Class name to reflect and cache
	 * @return \ReflectionClass
	 * @static
	 */
	public static function get($class)
	{
		if (!class_exists($class) && !interface_exists($class))
			throw new \Exception("Class or Interface" .$class. " does not exist");

		if (substr($class, 0, 1) == "\\")
			$class = substr($class, 1);

		if (false === ($idx = self::find($class))) {
			return self::put($class);
		}

		return self::$_cache[$idx];
	}

	/**
	 * Recursive implementation of binary search
	 * @param string $n Needle
	 * @param array $c Copy of cache
	 * @return mixed Returns int if $n is found false otherwise
	 */
	private static function find($n, $c = null)
	{
		if ($c == null)
			$c = self::$_cache;

		$l = count($c);   

		if ($l == 0) return false;
		if ($l == 1) return ($n == $c[0]->name) ? 0 : false;

		$m = (($l + ($l%2)) / 2);
		if ($m >= $l) $m--;

		switch (self::compare($c[$m]->name, $n))
		{
			case 1:
				$r = self::find($n, array_slice($c, 0, $m));
				if ($r === false) return false;
				return $m - ($m - $r);
			case -1:
				$r = self::find($n, array_slice($c, $m, ($l-$m)));
				if ($r === false) return false;
				return $r + $m;
			case 0:
				return $m;
		}
	}

	/**
	 * Implementation of insertion sort to keep $_cache sorted alphabetically
	 * @param string $n Class name
	 * @return \ReflectionClass
	 */
	private static function put($n)
	{
		$refl = new \ReflectionClass($n);

		$c = count(self::$_cache);
		while ($c) 
		{
			$r = self::compare(self::$_cache[$c-1]->name, $n);
			if ($r == -1) break;
			$c--;
		}

		array_splice(self::$_cache, $c, 0, array($refl));

		return $refl;
	}

	/**
	 * Run strcmp, clamp return value between 1 and -1
	 * @param string $a
	 * @param string $b
	 */
	private static function compare($a, $b) 
	{
		return min(max(strcmp($a,$b), -1), 1);
	}
}

