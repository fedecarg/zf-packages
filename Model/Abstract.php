<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Model
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Model
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */
abstract class Zf_Model_Abstract
{
    /**
     * @var null|Zf_Resource_Loader
     */
    protected $_resourceLoader = null;
    
    /**
     * @var string Class path
     */
    protected $_classPath = null;
    
    /**
     * Set path to class.
     *
     * @param string $path
     * @return void
     */
    public function setClassPath($path)
    {
        $this->_classPath = $path;    
    }
    
    /**
     * Set resource loader.
     * 
     * @param Zf_Resource_Loader
     * @return void
     */
    public function setResourceLoader(Zf_Resource_Loader $object)
    {
        $this->_resourceLoader = $object;
    }
    
    /**
     * Return resource loader.
     * 
     * @return Zf_Resource_Loader
     */
    public function getResourceLoader()
    {
        if (null === $this->_resourceLoader) {
            throw new Zf_Model_Exception('Dependency must be injected by Zend_Controller_Action.'); 
        }
        
        return $this->_resourceLoader; 
    }
    
    /**
     * Return model.
     * 
     * @param string $name Model name
     * @return object|boolean Resource or false
     */
    public function getModel($name)
    {
        return $this->getResourceLoader()->getModel($name); 
    }
    
    /**
     * Return DAO.
     * 
     * @param string $name DAO name
     * @return object|boolean Resource or false
     */
    public function getDao($name)
    {
        return $this->getResourceLoader()->getDao($name); 
    }
}
