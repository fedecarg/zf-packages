<?php
/**
 * Copyright (c) 2010, Federico Cargnelutti. All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgment:
 *    This product includes software developed by Federico Cargnelutti.
 * 4. Neither the name of Federico Cargnelutti nor the names of its contributors 
 *    may be used to endorse or promote products derived from this software without 
 *    specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY FEDERICO CARGNELUTTI "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL FEDERICO CARGNELUTTI BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Zf
 * @package     Zf_Db
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @copyright   Copyright (c) 2010 Federico Cargnelutti
 * @license     New BSD License
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Db
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @copyright   Copyright (c) 2010 Federico Cargnelutti
 * @license     New BSD License
 * @version     $Id: $
 */
class Zf_Db_ReplicationAdapter
{  
    const CONFIG_ARRAY        = 'db_config_array';
    const ACTIVE_CONNECTION   = 'db_connection_%s';
    const FAILED_CONNECTIONS  = 'db_failed_connections';
    
    const SUPPLIER_SERVER     = 'master';
    const CONSUMER_SERVER     = 'slave';
    
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
        $namespace = sprintf(self::ACTIVE_CONNECTION, strtolower($server));
        Zend_Registry::set($namespace, $conn);
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
        $namespace = sprintf(self::ACTIVE_CONNECTION, $server);
        if (Zend_Registry::isRegistered($namespace)) {
            return Zend_Registry::get($namespace);
        }
        
        $failed = array();
        if (Zend_Registry::isRegistered(self::ZEND_CACHE)) {
            $cache = Zend_Registry::get(self::ZEND_CACHE);
            $result = $cache->load(self::FAILED_CONNECTIONS);
            $failed = ($result && is_array($result)) ? $result : $failed;
        }
        
        $servers = $this->getListOfServers($server);
        $keys = (array) array_rand($servers, count($servers));
        foreach ($keys as $i => $key) {
            if (in_array($key, $failed)) {
                continue;
            }
            $connection = $this->createConnection($servers[$key]);
            if ($connection instanceof Zend_Db_Adapter_Abstract) {
                $this->setConnection($connection, $server);
                return $connection;
            }
            if (Zend_Registry::isRegistered(self::ZEND_CACHE)) {
                $failed[] = $key;
                $cache = Zend_Registry::get(self::ZEND_CACHE);
                $cache->save(array_unique($failed), self::FAILED_CONNECTIONS, array(), 30);
            }
        }
        
        throw new Zf_Db_Exception(sprintf('Unable to connect to "%s" server', $server));
    }
    
    /**
     * Return list of database servers that will be used to create a 
     * connection.
     * 
     * @param string $server master (supplier) or slave (consumers)
     * @return array
     */
    public function getListOfServers($server)
    {
        $config = $this->getConfigFromRegistry();
        $servers = (isset($config['servers'])) ? $config['servers'] : array();
        $masterServers = (isset($config['master_servers'])) ? $config['master_servers'] : 1;
        if (self::SUPPLIER_SERVER === $server) {
            $servers = array_slice($servers, 0, $masterServers);
        } elseif (self::CONSUMER_SERVER === $server) {
            $masterRead = (isset($config['master_read'])) ? $config['master_read'] : false;
            if (false === $masterRead) {
                $servers = array_slice($servers, $masterServers, count($servers), true);
            }
        }
        return $servers;
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
            Zend_Registry::set(self::CONFIG_ARRAY, $config);
        }
        
        return $config;
    }
    
    /**
     * Create an instance of the adapter class.
     *
     * @param array $server Server info
     * @return Zend_Db_Adapter_Abstract|false
     * @see Zend_Db
     */
    public function createConnection($server)
    {
        $config = $this->getConfigFromRegistry();
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
}