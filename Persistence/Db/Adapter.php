<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Persistence
 * @author      Federico Cargnelutti <fedecarg@yahoo.co.uk>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Persistence
 * @author      Federico Cargnelutti <fedecarg@yahoo.co.uk>
 * @version     $Id: $
 */
class Zf_Persistence_Db_Adapter
{
    /**
     * @var Zend_Db_Adapter_Abstract|null
     */
    protected $_db = null;
    
    /**
     * Set database adapter.
     * 
     * @param Zend_Db_Adapter_Abstract
     * @return void
     */
    public function setAdapter(Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
    }
    
    /**
     * Return database adapter.
     * 
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        if (null === $this->_db) {
            if (!Zend_Registry::isRegistered('Zend_Db')) {
                throw new Zf_Dao_Exception('Zend_Db is not registered');
            }
            $this->setAdapter(Zend_Registry::get('Zend_Db'));
        }
        
        return $this->_db; 
    }
}