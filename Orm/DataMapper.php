<?php
/**
 * Copyright (c) 2010, Federico Cargnelutti. All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgment:
 *    This product includes software developed by Federico Cargnelutti.
 * 4. Neither the name of Federico Cargnelutti nor the names of its contributors 
 *    may be used to endorse or promote products derived from this software without 
 *    specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY FEDERICO CARGNELUTTI "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL FEDERICO CARGNELUTTI BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Zf
 * @package     Zf_Orm
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @copyright   Copyright (c) 2010 Federico Cargnelutti
 * @license     New BSD License
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Orm
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @copyright   Copyright (c) 2010 Federico Cargnelutti
 * @license     New BSD License
 * @version     $Id: $
 */
class Zf_Orm_DataMapperException extends Zf_Orm_Exception {}

class Zf_Orm_DataMapper
{
    /**
     * @var array
     */
    protected $map = array();

    /**
     * Class constructor.
     *
     * @param array $map
     * @return void
     */
    public function __construct(array $map = null)
    {
        if (null !== $map) {
            $this->setMap($map);
        }
    }

    /**
     * Set map array. 
     *
     * @param array $map
     * @return void
     */
    public function setMap(array $map)
    {
        $this->map = $map;
    }

    /**
     * Return map array.
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Append fields to the map array.
     *
     * @param array
     * @return void
     */
    public function append(array $fields)
    {
        $this->setMap(array_merge($this->getMap(), $fields));
    }

    /**
     * Assign property values.
     *
     * @param Zf_Orm_Entity $entity
     * @param array $element
     * @return void
     * @throws Zf_Orm_DataMapperException
     */
    public function assign(Zf_Orm_Entity $entity, array $element)
    {
        foreach ($element as $key => $value) {
            $map = $this->getMap();
            if (! array_key_exists($key, $map)) {
                throw new Zf_Orm_DataMapperException(sprintf('No such field "%s"', $key));
            }
            $property = $map[$key];
            if (! property_exists($entity, $property)) {
                $message = sprintf('Property "%s" not defined in %s', $property, get_class($entity));
                throw new Zf_Orm_DataMapperException($message);
            }
            $entity->$property = $value;
        }
        return $entity;
    }

    /**
     * Map fields to properties.
     *
     * @param Zf_Orm_Entity $entity
     * @return array
     */
    public function map(Zf_Orm_Entity $entity)
    {
        $array = array();
        foreach ($this->getMap() as $field => $property) {
            $array[$field] = $entity->$property;
        }
        return $array;
    }
}
