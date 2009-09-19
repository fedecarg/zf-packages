<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Domain
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Domain
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */
class Zf_Domain_Entity
{
    /**
     * Define properities.
     *
     * @param array $properties
     * @return void
     * @throws Zf_Domain_Exception
     */
    public function define(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (!property_exists($this, $property)) {
                $message = sprintf('Property "%s" not defined in %s', $property, get_class($this));
                throw new Zf_Domain_Exception($message);
            }
            $this->$property = $value;
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
            if (property_exists($this, $property)) {
                return $this->$property;
            }           
        } elseif ('set' === $type) {
            if (property_exists($this, $property)) {
                $this->$property = $args[0];
                return;
            }
        }
        
        $message = 'Invalid method call: ' . get_class($this).'::'.$method.'()';
        throw new Zf_Domain_Exception($message);
    }
}
