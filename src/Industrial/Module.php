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

/**
 * Abstract module.
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.1.1
 * @since 0.1
 */
abstract class Module
{
    /**
     * Factory instance. Will be set only during the scope of the call 
     * to configure()
     *
     * @var \Industrial\Factory
     */
    protected $factory = null;

    /**
     * Create a binder for the given class name and add it to the factory
     * 
     * @param string $class
     * @uses \Industrial\Factory::addBound()
     * @return \Industrial\Binder
     * @throws \Exception
     * @final 
     */
    protected final function bind($class)
    {
        if (!$this->factory)
            throw new \Exception("bind must only be call from within the config() method");

        $bound = new Binder($this->factory);
        $bound->bind($class);
        $this->factory->addBound($bound);
        return $bound;
    }

    /**
     * Configure module.
     * @param \Industrial\Factory
     */
    public final function configure(Factory $factory) 
    {
        $this->factory = $factory;
        $this->config();
        $this->factory = null;
    }

    /**
     * Provided for module configuration.
     * @abstract
     */
    abstract protected function config();
}
