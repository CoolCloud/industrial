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
 * Dependency Injection factory
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.1
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
     * @var \Industrial\Module
     */
    private $_module = null;

    /**
     * @var boolean
     */
    private $_configured = false;

    /**
     * Constructor
     * @param \Industrial\Module
     */
    public function __construct(Module $module)
    {
        $this->_module = $module;
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
    public function getBound($className) 
    {
        foreach ($this->_bound as $bound) {
            if ($bound->is($className)) 
                return $bound;
        }
    }

    /**
     * Instantiate a class according to rules in it's binder.
     * @param string $class
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function make($class)
    {
        if (!$this->_configured) {
            $this->_module->configure($this);
            $this->_configured = true;
        }

        if ($binder = $this->getBound($class)) {
            return $binder->build();
        }

        throw new \InvalidArgumentException("Class: $class has not been bound");
    }
}
