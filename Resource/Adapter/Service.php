<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Resource
 * @subpackage  Adapter
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Resource
 * @subpackage  Adapter
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */
class Zf_Resource_Adapter_Service extends Zf_Resource_Adapter
{    
    /**
     * Return an instance of the front controller.
     *
     * @return Zend_Controller_Front
     */
    public function getFrontController()
    {
        return Zend_Controller_Front::getInstance();
    }
    
    /**
     * Return request object.
     * 
     * @param string $name Model name
     * @return Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        return $this->getFrontController()->getRequest(); 
    }
    
    /**
     * Return Model.
     * 
     * @param string $name Model name
     * @return Zf_Resource_Adapter
     */
    public function getModel($name)
    {
        return $this->getResourceLocator()->getModel($name); 
    }
}
