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
     * @var null|string Class path
     */
    protected $_classPath = null;
    
    /**
     * Set class path.
     *
     * @param string $path
     * @return void
     */
    public function setClassPath($path)
    {
        $this->_classPath = $path;    
    }
    
    /**
     * Return class path.
     *
     * @return string|null
     */
    public function getClassPath()
    {
        return $this->_classPath;    
    }
    
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
     * @param string $className
     * @param string $classPath
     * @return Zf_Resource_LoaderAdapter
     */
    public function getResource($className, $classPath)
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
        
        return Zend_Registry::get($resourceName);
    }
    
    /**
     * Return a sigle instance of a given model name.
     * 
     * @param string $name Model name
     * @return Zf_Resource_LoaderAdapter
     */
    public function getModel($name)
    {
        $className = $name . 'Model';
        $classPath = APPLICATION_PATH . '/models';
        
        return $this->getResource($className, $classPath);

    }
    
    /**
     * Return a sigle instance of a given service name.
     * 
     * @param string $name Service name
     * @return Zf_Resource_LoaderAdapter
     */
    public function getService($name)
    {
        $className = $name . 'Service';
        $classPath = APPLICATION_PATH . '/services';
        
        return $this->getResource($className, $classPath);
    }
    
    /**
     * Return DAO.
     * 
     * @param string $name DAO name
     * @return object Instance of DB Adapter
     */
    public function getDao($name)
    {
        $className = $name . 'Dao';
        $classPath = APPLICATION_PATH . '/daos';
        
        return $this->getResource($className, $classPath);
    }
}