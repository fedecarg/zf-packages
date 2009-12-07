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
     * @return Zf_Resource_LoaderAdapter
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
        $className = $name . self::MODEL_CLASS_SUFFIX;
        $classPath = APPLICATION_PATH . '/' . self::MODEL_DIR;
        
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
        $className = $name . self::SERVICE_CLASS_SUFFIX;
        $classPath = APPLICATION_PATH . '/' . self::SERVICE_DIR;
        
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
        $className = $name . self::DAO_CLASS_SUFFIX;
        $classPath = APPLICATION_PATH . '/' . self::DAO_DIR;
        
        return $this->createResource($className, $classPath);
    }
}