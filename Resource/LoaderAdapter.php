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
class Zf_Resource_LoaderAdapter
{
    /**
     * @var null|Zf_Resource_Loader
     */
    protected $_resourceLoader = null;
    
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
     * @return Zf_Resource_Exception
     */
    public function getResourceLoader()
    {
        if (null === $this->_resourceLoader) {
            throw new Zf_Resource_Exception('Zf_Resource_Loader is not defined'); 
        }
        
        return $this->_resourceLoader; 
    }
}
