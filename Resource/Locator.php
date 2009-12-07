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
class Zf_Resource_Locator
{
    const MODEL_CLASS_SUFFIX    = 'Model';
    const MODEL_DIR             = 'models';
    
    const SERVICE_CLASS_SUFFIX  = 'Service';
    const SERVICE_DIR           = 'services';
    
    const DAO_CLASS_SUFFIX      = 'Dao';
    const DAO_DIR               = 'daos';
    
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
     * Check whether a resource has been registered or not.
     * 
     * @param string $resourceName
     * @return boolean
     */
    public function hasResource($resourceName)
    {
        return Zend_Registry::isRegistered($resourceName);
    }    
    
    /**
     * Locate resource.
     * 
     * @param string $className
     * @param string $classPath
     * @return void
     */
    public function locate($className, $classPath)
    {
        $file = $classPath.'/'.$className.'.php';
        if ($this->hasResource($className)) {
            throw new Zf_Resource_Exception('Resource exists: ' . $className);
        } elseif (!file_exists($file)) {
            throw new Zf_Resource_Exception('No such file: '. $file);
        }
        
        require_once $file;
        $obj = new $className;
        if ($obj instanceof Zf_Resource_Adapter) {
            $obj->setClassPath($classPath.'/'.basename($classPath));
            $obj->setResourceLocator($this);
        }
        $this->setResource($className, $obj);
    }
    
    /**
     * Return a sigle instance of a model object.
     * 
     * @param string $name Model name
     * @return Zf_Resource_Adapter
     */
    public function getModel($name)
    {
        $className = $name . self::MODEL_CLASS_SUFFIX;
        if (!$this->hasResource($className)) {
            $classPath = APPLICATION_PATH . '/' . self::MODEL_DIR;
            $this->locate($className, $classPath);
        }
        
        return $this->getResource($className);

    }
    
    /**
     * Return a sigle instance of a service object.
     * 
     * @param string $name Service name
     * @return Zf_Resource_Adapter
     */
    public function getService($name)
    {
        $className = $name . self::SERVICE_CLASS_SUFFIX;
        if (!$this->hasResource($className)) {
            $classPath = APPLICATION_PATH . '/' . self::SERVICE_DIR;
            $this->locate($className, $classPath);
        }
        
        return $this->getResource($className);
    }
    
    /**
     * Return a sigle instance of a data access object.
     * 
     * @param string $name
     * @return object
     */
    public function getDao($name)
    {
        $className = $name . self::DAO_CLASS_SUFFIX;
        if (!$this->hasResource($className)) {
            $classPath = APPLICATION_PATH . '/' . self::DAO_DIR;
            $this->locate($className, $classPath);
        }
        
        return $this->getResource($className);
    }
}