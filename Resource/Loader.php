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
     * Return resource.
     * 
     * @param string $resourceName
     * @return object
     */
    public function getResource($resourceName)
    {        
        return Zend_Registry::get($resourceName);
    }
    
    
    /**
     * Create resource.
     * 
     * @param string $className
     * @param string $classPath
     * @return Zf_Resource_LoaderAdapter
     */
    public function createResource($className, $classPath)
    {
        $resourceName = 'Resource_' . $className;
        if (!Zend_Registry::isRegistered($resourceName)) {
            require_once $classPath.'/'.$className.'.php';
            $obj = new $className;
            if ($obj instanceof Zf_Resource_LoaderAdapter) {
                $obj->setClassPath($classPath.'/'.basename($classPath));
                $obj->setResourceLoader($this);
            }
            $this->setResource($resourceName, $obj); 
        }
        
        return $this->getResource($resourceName);
    }
    
    /**
     * Return a sigle instance of a model object.
     * 
     * @param string $name Model name
     * @return Zf_Resource_LoaderAdapter
     */
    public function getModel($name)
    {
        $className = $name . 'Model';
        $classPath = APPLICATION_PATH . '/models';
        
        return $this->createResource($className, $classPath);

    }
    
    /**
     * Return a sigle instance of a service object.
     * 
     * @param string $name Service name
     * @return Zf_Resource_LoaderAdapter
     */
    public function getService($name)
    {
        $className = $name . 'Service';
        $classPath = APPLICATION_PATH . '/services';
        
        return $this->createResource($className, $classPath);
    }
    
    /**
     * Return a sigle instance of a data access object.
     * 
     * @param string $name
     * @return object
     */
    public function getDao($name)
    {
        $className = $name . 'Dao';
        $classPath = APPLICATION_PATH . '/daos';
        
        return $this->createResource($className, $classPath);
    }
}