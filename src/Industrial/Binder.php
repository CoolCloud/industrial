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
 * @version 0.1
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
 * @version 0.1
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
    private $_reflection = null;

    /** 
     * @var array
     */
    private $_constructArgs = array();

    /**
     * @var array
     */
    private $_methods = array();

    /**
     * Constuctor.
     *
     * @param string $className
     */
    public function __construct($className) 
    {
        if (!class_exists($className)) 
            throw new \InvalidArgumentException("Class: $className does not exist or could not be found");

        $this->_className = $className;
        $this->_reflection = new \ReflectionClass($className);
    }

    /**
     * Provide arguments for the __construct method of the bound class
     * @param array $args
     * @return \Industrial\Binder Provide Fluent Interface
     * @todo Implement checks to ensure that the proper argument types
             were passed.
     */
    public function construct($args = null)
    {
        $this->_constructArgs = $args;
        return $this;
    }

    /**
     * Used to set a chain of methods to call during the instantiation of 
     * the bound class
     * @return \Industrial\Binder Provide Fluent Interface
     */
    public function __call($method,$args)
    {
        $this->_methods[] = [$method,$args];
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
        if ($this->_constructArgs) {
            $obj = $this->_reflection->newInstanceArgs($this->_constructArgs);
        } else {
            $obj = $this->_reflection->newInstance();
        }

        foreach ($this->_methods as $method) {
            call_user_func_array(array($obj,$method[0]),$method[1]);
        }

        return $obj;
    }
}
