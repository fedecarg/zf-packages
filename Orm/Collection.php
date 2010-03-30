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
class Zf_Orm_Collection implements Countable, Iterator, ArrayAccess
{
    /**
     * @var array 
     */
    protected $elements = array();
    
    /**
     * @var integer
     */
    protected $iteratorIndex = 0;
    
    /**
     * @var integer
     */
    protected $iteratorCount = 0;

    /**
     * Class constructor.
     *
     * @param array $elements
     */
    public function __construct(array $elements = array())
    {
        $this->elements = $elements;
    }

    /**
     * Returns an array containing all of the elements in this collection.
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->elements;
    }

    /**
     * Returns the number of elements.
     *
     * @return integer
     */
    public function count()
    {
        return $this->iteratorCount;
    }

    /**
     * Returns the current element.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->elements);
    }

    /**
     * Returns the key of the current element.
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->elements);
    }

    /**
     * Returns the next element or false if at the end of elements array.
     *
     * @return mixed
     */
    public function next()
    {
        $this->iteratorIndex++;
        return next($this->elements);
    }

    /**
     * Rewinds the iterator index.
     *
     * @return mixed
     */
    public function rewind()
    {
        $this->iteratorIndex = 0;
        reset($this->elements);
    }

    /**
     * Checks if the current index is valid.
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->iteratorIndex < $this->iteratorCount;
    }

    /**
     * Offset check for the ArrayAccess interface.
     *
     * @param mixed $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->elements);
    }

    /**
     * Getter for the ArrayAccess interface.
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->elements[$key];
    }

    /**
     * Setter for the ArrayAccess interface.
     *
     * @param mixed $key
     * @param mixed $element
     * @return void
     */
    public function offsetSet($key, $element)
    {
        $this->elements[$key] = $element;
        $this->iteratorCount = count($this->elements);
    }

    /**
     * Unsetter for the ArrayAccess interface.
     *
     * @param mixed $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->elements[$key]);
        $this->iteratorCount = count($this->elements);
    }

    /**
     * Adds an element to the end of the internal elements array.
     *
     * @param mixed $element.
     * @return void
     */
    public function append($element)
    {
        $this->elements[] = $element;
        $this->iteratorCount = count($this->elements);
    }
}
