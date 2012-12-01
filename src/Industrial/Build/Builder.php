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

/**
 * Builder.
 *
 * @package pihi/industrial
 * @author Isaac Hildebrandt <isaac@pihimedia.com>
 * @copyright 2012 
 * @license http://www.apache.org/licenses/LICENSE-2.0.txt Apache Software License
 * @version 0.2.0
 * @since 0.1
 */
class Builder
{
    private $_factory;

    private $_process = array();

    private $_params = array();

    public function __construct (\Industrial\Factory $factory)
    {
        $this->_factory = $factory;
    }

    public function __clone ()
    {
        $process = array();
        foreach ($this->_process as $proc) $process[] = $proc;
        $this->_process = $process;
    }

    public function addAction(Action\IAction $action) 
    {
        $process = $action->getProcessor();
        if (!is_callable($process)) {
            throw new \Exception("Action processor must be callable.");
        }
        $this->_process[] = $process;
    }

    /**
     * Provide arguments to be passed to Injector
     * @param string $param Name of constructor parameter
     * @param mixed $value Value to be passed
     * @return \Industrial\Build\Builder Provide fluent interface
     */
    public function with($param, $value)
    {
        $this->_params[$name] = $value;
        return $this;
    }

    /**
     * @param \Industrial\Factory $factory
     * @return object
     */
    public function build()
    {
        $obj = null;
        foreach ($this->_process as $process) {
            $process($this->_factory,$obj);
        }
        return $obj;
    }
}
