<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Resource
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Resource
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */
class Zf_Resource_Loader
{
    /**
     * @param string $name Resource name
     * @param object $obj
     * @return void
     */
    public function setResource($name, $obj)
    {
        Zend_Registry::set($name, $obj);
    }

    /**
     * @param string $name Resource name
     * @return null|obj
     */
    public function getResource($name)
    {
        if (! Zend_Registry::isRegistered($name)) {
            return null;
        }
        return Zend_Registry::get($name);
    }

    /**
     * @param string $name Service name
     * @return object|null
     */
    public function getService($name)
    {
        $className = $name . 'Service';
        if (! Zend_Registry::isRegistered($className)) {
            $dir = APPLICATION_PATH . '/services';
            require_once $dir . '/' . $className . '.php';
            $this->setResource($className, new $className($this));
        }
        return $this->getResource($className);
    }

    /**
     * @param string $name Model name
     * @return object|null
     */
    public function getModel($name)
    {
        $className = $name . 'Model';
        if (! Zend_Registry::isRegistered($className)) {
            $dir = APPLICATION_PATH . '/models';
            require_once $dir . '/' . $className . '.php';
            $this->setResource($className, new $className($this));
        }
        return $this->getResource($className);
    }
}