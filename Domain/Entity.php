<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Domain
 * @author      Federico Cargnelutti <fedecarg@yahoo.co.uk>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Domain
 * @author      Federico Cargnelutti <fedecarg@yahoo.co.uk>
 * @version     $Id: $
 */
class Zf_Domain_Entity
{
    /**
     * Class properties
     * @var array
     */
    protected $properties = array();
    
    /**
     * Constructor.
     *
     * @param array $properties
     * @return void
     */
    public function __construct($properties)
    {
        $this->define($properties);
    }
    
    /**
     * Define class properties.
     *
     * @param array $properties
     * @return void
     */
    public function define(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $message = sprintf('Class property "%s" already defined.', $property);
                throw new Zf_Domain_Exception($message);
            }
            $this->properties[$property] = $value;
        }
    }
    
    /**
     * Get property value.
     *
     * @param string $property
     * @return mixed
     * @throws Zf_Domain_Exception
     */
    public function __get($property) 
    {
        if (array_key_exists($property, $this->properties)) {
            return $this->properties[$property];
        } elseif (property_exists($this, $property)) {
            return $this->$property;
        }
        throw new Zf_Domain_Exception('Undefined property: ' . $property);
    }
    
    /**
     * Set property value.
     *
     * @param string $property
     * @param mixed $value
     * @return void
     * @throws Zf_Domain_Exception
     */
    public function __set($property, $value) 
    {
        if (array_key_exists($property, $this->properties)) {
            $this->properties[$property] = $value;
        } elseif (property_exists($this, $property)) {
            $this->$property = $value;
        }
        throw new Zf_Domain_Exception('Invalid property: ' . $property);
    }
    
    /**
     * Enter description here...
     *
     * @param string $property
     * @return boolean
     */
    public function __isset($property) 
    {
        if (isset($this->properties[$property]) || property_exists($this, $property)) {
            return true;
        }
        return false;
    }
    
    /**
     * Enter description here...
     *
     * @param string $property
     * @return void
     */
    public function __unset($property) 
    {
        if (isset($this->$property)) {
            $this->$property = null;
        }
    }
    
    /**
     * Create setter and getter methods.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws Zf_Domain_Exception
     */
    public function __call($method, $args)
    {
        $type = substr($method, 0, 3);
        $property = strtolower($method[3]) . substr($method, 4);

        if ('get' === $type) {
            if (array_key_exists($property, $this->properties)) {
                return $this->properties[$property];
            } elseif (property_exists($this, $property)) {
                return $this->$property;
            }            
        } elseif ('set' === $type) {
            if (array_key_exists($property, $this->properties)) {
                $this->properties[$property] = $args[0];
                return;
            } elseif (property_exists($this, $property)) {
                $this->$property = $args[0];
                return;
            }
        }
        
        $message = 'Invalid method call: ' . get_class($this).'::'.$method.'()';
        throw new Zf_Domain_Exception($message);
    }
}