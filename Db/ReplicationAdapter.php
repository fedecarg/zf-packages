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
class Zf_Db_ReplicationAdapter
{
    const CONFIG_ARRAY        = 'db_config_array';
    const CONNECTION          = 'db_connection_%s';
    const FAILED_CONNECTIONS  = 'db_failed_connections';
    const ZEND_CACHE          = 'Zend_Cache';
    
    /**
     * Set an instance of Zend_Db_Adapter_Abstract.
     * 
     * @param Zend_Db_Adapter_Abstract $conn
     * @param string $server Options: master, slave
     * @return void
     */
    public function setConnection(Zend_Db_Adapter_Abstract $conn, $server)
    {
        $key = sprintf(self::CONNECTION, ucfirst(strtolower($server)));
        Zend_Registry::set($key, $conn);
    }
    
    /**
     * Return an instance of Zend_Db_Adapter_Abstract.
     * 
     * @param string $server Options: master, slave
     * @return Zend_Db_Adapter_Abstract
     * @throws Zf_Db_Exception
     */
    public function getConnection($server)
    {
        $server = strtolower($server);
        $key = sprintf(self::CONNECTION, ucfirst(strtolower($server)));
        if (Zend_Registry::isRegistered($key)) {
            return Zend_Registry::get($key);
        } elseif (!Zend_Registry::isRegistered(self::CONFIG_ARRAY)) {
            throw new Zf_Db_Exception('Config array undefined: ' . self::CONFIG_ARRAY);
        }
        
        $config = Zend_Registry::get(self::CONFIG_ARRAY);
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        
        $servers = (isset($config['servers'])) ? $config['servers'] : array();
        $masterServers = (isset($config['master_servers'])) ? $config['master_servers'] : 1;
        if ('master' === $server) {
            $servers = array_slice($servers, 0, $masterServers);
        } elseif ('slave' === $server) {
            $masterRead = (isset($config['master_read'])) ? $config['master_read'] : false;
            if (false === $masterRead) {
                $servers = array_slice($servers, $masterServers, count($servers), true);
            }
        }
        
        $failed = array();
        if (Zend_Registry::isRegistered(self::ZEND_CACHE)) {
            $cache = Zend_Registry::get(self::ZEND_CACHE);
            $result = $cache->load(self::FAILED_CONNECTIONS);
            $failed = ($result && is_array($result)) ? $result : $failed;
        }
        
        $keys = (array) array_rand($servers, count($servers));
        foreach ($keys as $i => $key) {
            if (in_array($key, $failed)) {
                continue;
            }
            $connection = $this->createConnection($servers[$key], $config);
            if ($connection instanceof Zend_Db_Adapter_Abstract) {
                $this->setConnection($connection, $server);
                return $connection;
            }
            if (Zend_Registry::isRegistered(self::ZEND_CACHE)) {
                $failed[] = $key;
                $this->storeFailedConnections($failed);
            }
        }
        
        throw new Zf_Db_Exception(sprintf('Unable to connect to "%s" server', $server));
    }
    
    /**
     * Create an instance of the adapter class.
     *
     * @param array $server Server info
     * @param array $config Configuration array
     * @return Zend_Db_Adapter_Abstract|false
     * @see Zend_Db
     */
    public function createConnection($server, $config)
    {
        foreach ($config as $key => $value) {
            if ('servers' !== $key && !array_key_exists($key, $server)) {
                $server[$key] = $value;
            }
        }
        
        $db = Zend_Db::factory($config['adapter'], $server);
        if ($this->hasConnection($db)) {
            return $db;
        } 
        return false;
        
    }
    
    /**
     * Verify that Zend_Db_Adapter_Abstract holds a valid connection.
     * 
     * @param $db Zend_Db_Adapter_Abstract
     * @return boolean
     */
    public function hasConnection(Zend_Db_Adapter_Abstract $db)
    {
        try {
            return ($db->getConnection()) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Store failed connection indexes into a cache record.
     *
     * @param array $indexes Failed connections
     * @return boolean
     */
    public function storeFailedConnections($indexes)
    {
        $cache = Zend_Registry::get(self::ZEND_CACHE);
        return $cache->save(array_unique($indexes), self::FAILED_CONNECTIONS);
    }
}