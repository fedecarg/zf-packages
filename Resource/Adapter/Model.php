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
class Zf_Resource_Adapter_Model extends Zf_Resource_LoaderAdapter
{    
    /**
     * Return Model.
     * 
     * @param string $name Model name
     * @return Zf_Resource_LoaderAdapter
     */
    public function getModel($name)
    {
        return $this->getResourceLoader()->getModel($name); 
    }
    
    /**
     * Return DAO.
     * 
     * @param string $name DAO name
     * @return object Instance of DB Adapter
     */
    public function getDao($name)
    {
        return $this->getResourceLoader()->getDao($name); 
    }
}
