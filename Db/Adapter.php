<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Db
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Db
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */
class Zf_Db_Adapter
{
	const CONFIG_ARRAY       = 'db_config_array';
	const ACTIVE_CONNECTION  = 'db_connection_instance';
	
    /**
     * Set an instance of Zend_Db_Adapter_Abstract.
     * 
     * @param Zend_Db_Adapter_Abstract $conn
     * @return void
     */
    public function setConnection(Zend_Db_Adapter_Abstract $conn)
    {
        Zend_Registry::set(self::ACTIVE_CONNECTION, $conn);
    }
    
    /**
     * Return an instance of Zend_Db_Adapter_Abstract.
     * 
     * @return Zend_Db_Adapter_Abstract
     * @throws Zf_Db_Exception
     */
    public function getConnection()
    {
        if (Zend_Registry::isRegistered(self::ACTIVE_CONNECTION)) {
            return Zend_Registry::get(self::ACTIVE_CONNECTION);
        }
        
        $connection = $this->createConnection();
        if ($connection instanceof Zend_Db_Adapter_Abstract) {
            $this->setConnection($connection);
            return $connection;
        }
        
        throw new Zf_Db_Exception('Unable to connect to database server');
    }
    
    
    /**
     * Create an instance of the adapter class.
     *
     * @return Zend_Db_Adapter_Abstract
     * @see Zend_Db
     */
    public function createConnection()
    {
        $config = $this->getConfigFromRegistry();
        return Zend_Db::factory($config['adapter'], $config);   
    }
    
    /**
     * Retrieve configuration array from Zend_Registry.
     *
     * @return array
     * @throws Zf_Db_Exception
     */
    public function getConfigFromRegistry()
    {
        if (!Zend_Registry::isRegistered(self::CONFIG_ARRAY)) {
            throw new Zf_Db_Exception('Config array undefined: ' . self::CONFIG_ARRAY);
        }
        
        $config = Zend_Registry::get(self::CONFIG_ARRAY);
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        
        return $config;
    }
}
