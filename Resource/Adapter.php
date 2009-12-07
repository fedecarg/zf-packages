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
class Zf_Resource_Adapter
{
    /**
     * @var null|Zf_Resource_Locator
     */
    protected $_resourceLocator = null;
    
    /**
     * @var null|string Class path
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
     * Return class path.
     *
     * @return string|null
     */
    public function getClassPath()
    {
        return $this->_classPath;    
    }
    
    /**
     * Set resource locator.
     * 
     * @param Zf_Resource_Locator
     * @return void
     */
    public function setResourceLocator(Zf_Resource_Locator $object)
    {
        $this->_resourceLocator = $object;
    }
    
    /**
     * Return resource locator.
     * 
     * @return Zf_Resource_Locator
     */
    public function getResourceLocator()
    {
        if (null === $this->_resourceLocator) {
            throw new Zf_Resource_Exception('Zf_Resource_Locator is not defined'); 
        }
        
        return $this->_resourceLocator; 
    }
}
