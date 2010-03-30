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
