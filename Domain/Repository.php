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
abstract class Zf_Domain_Repository
{
    public function __call($method, $arguments)
    {
        $property = lcfirst(substr($method, 3));
        if (!property_exists($property)) {
            throw new Zf_Domain_Exception(sprintf('Undefined property %s', $property));
        }
        if (null === $this->$property && array_key_exists($property, $this->inject)) {
            $this->$property = new $this->inject[$property];
        }
        return $this->$property;
    }
}
