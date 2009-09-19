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
     * Set resource.
     * 
     * @param string $resourceName
     * @param object $obj
     * @return void
     */
    public function setResource($resourceName, $obj)
    {
        Zend_Registry::set($resourceName, $obj);
    }
    
    /**
     * Return model oject.
     * 
     * @param string $name Model name
     * @return object|boolean Resource or false
     */
    public function getModel($name)
    {
        $className = $name . 'Model';
        $resourceName = 'Resource_' . $className;
        if (!Zend_Registry::isRegistered($resourceName)) {
            $dir = APPLICATION_PATH . '/models';
            require_once $dir.'/'.$className.'.php';
            $obj = new $className;
            if ($obj instanceof Zf_Model_Abstract) {
                $obj->setClassPath($dir.'/'.$name);
                $obj->setResourceLoader($this);
            }
            $this->setResource($resourceName, $obj); 
        }
        return Zend_Registry::get($resourceName); 
    }
    
    /**
     * Return DAO.
     * 
     * @param string $name DAO name
     * @return object|boolean Resource or false
     */
    public function getDao($name)
    {
        $className = $name . 'Dao';
        $resourceName = 'Resource_' . $className;
        if (!Zend_Registry::isRegistered($resourceName)) {
            $dir = APPLICATION_PATH . '/daos';
            require_once $dir.'/'.$className.'.php';
            $obj = new $className;
            $this->setResource($resourceName, $obj); 
        }
        return Zend_Registry::get($resourceName); 
    }
}
