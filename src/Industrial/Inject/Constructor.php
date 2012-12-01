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

    /**
     * @param \Industrial\Factory $factory
     */
    public function __construct(\Industrial\Factory $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * Construct an object by injecting available parameters
     * @param \ReflectionClass $refl
     */
    public function construct(\ReflectionClass $refl)
    {
        if ($refl->isAbstract() || $refl->isInterface())
            throw new Exception ("Cannot construct");

        if ($constr = $refl->getConstructor()) {
            $params = $constr->getParameters();

            $inj_params = array();
            foreach ($params as $param) {
                if (null === ($pc = $param->getClass())) {
                    if ($param->isArray())
                        throw new Exception("Injection does not support array arguments");
                    if (!$param->isDefaultValueAvailable())
                        throw new Exception("Cannot inject not-typed argument without default value");
                    $inj_params[] = $param->getDefaultValue();
                } else {
                    try {
                        $obj = $this->_factory->make($pc->name);
                    } catch (\Exception $e) {
                        throw new Exception("Caught Exception while injecting constructor parameter", 0, $e);
                    }
                    $inj_params[] = $obj;
                }
            }

            return $refl->newInstanceArgs($inj_params);
        }

        return $refl->newInstanceWithoutConstructor();
    }

    public function properties()
    {
    }
}


