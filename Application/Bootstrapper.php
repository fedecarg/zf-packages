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
 * @package     Zf_Application
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @copyright   Copyright (c) 2010 Federico Cargnelutti
 * @license     New BSD License
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Application
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @copyright   Copyright (c) 2010 Federico Cargnelutti
 * @license     New BSD License
 * @version     $Id: $
 */
abstract class Zf_Application_Bootstrapper
{
    // Environments
    const ENV_LOCAL        = 'loc';
    const ENV_DEVELOPMENT  = 'dev';
    const ENV_TEST         = 'tst';
    const ENV_INTEGRATION  = 'int';
    const ENV_STAGING      = 'stg';
    const ENV_DEMO         = 'dmo';
    const ENV_PRODUCTION   = 'prd';
    
    /**
     * @var array
     */
    private $config = array();
    
    /**
     * @var boolean
     */
    private $isCli = false;
     
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        if (! defined('APPLICATION_PATH')) {
            throw new Zend_Exception('Use of undefined constant APPLICATION_PATH ');
        } elseif (! defined('APPLICATION_ENV')) {
            throw new Zend_Exception('Use of undefined constant APPLICATION_ENV');
        }
        $this->isCli = (PHP_SAPI == 'cli') ? true : false;
        
        $this->initErrorHandler();
        $this->initAutoloader();
        $this->initServerVars();
        $this->initConfig();
    }

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
     * Initialize components. 
     * 
     * @return void
     */
    public function run()
    {
        $this->initOrm();
        if (false === $this->isCli || self::ENV_TEST == APPLICATION_ENV) {
            $this->initSession();
        }
        $this->initLog();
        $this->initControllers();
        $this->initRouter();
        $this->initCache();
        $this->initLayout();
    }
    
    /**
     * Initialize error handler.
     * 
     * @return void
     */
    public function initErrorHandler()
    {
        date_default_timezone_set('Europe/London');
        if (self::ENV_PRODUCTION === APPLICATION_ENV) {
            error_reporting(0);
            ini_set('display_errors', 0);
        } else {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', 1);
        }
    }

    /**
     * Initialize autoloader so we don't have to explicitely 
     * require each class.
     * 
     * @return void
     */
    public function initAutoloader()
    {
        $path = realpath(APPLICATION_PATH . '/../lib');
        if (in_array(APPLICATION_ENV, array(self::ENV_LOCAL, self::ENV_TEST))) {
            $path = $path . PATH_SEPARATOR . get_include_path();
        }
        set_include_path($path);
        
        require_once 'Zend/Loader/Autoloader.php';
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->registerNamespace('Zf_');
        $loader->registerNamespace(APPLICATION_NAME . '_');
    }
    

    /**
     * Initialize server variables.
     * 
     * @return void
     */
    public function initServerVars()
    {
        if (in_array(APPLICATION_ENV, array(self::ENV_LOCAL, self::ENV_TEST))) {
            $_SERVER['SERVER_ADDR'] = '127.0.0.1';
        }
    }

    /**
     * Initialize Zend_Config.
     * 
     * @return void
     */
    public function initConfig()
    {
        $this->config = new Zend_Config(include APPLICATION_PATH . '/config/settings.php');
        Zend_Registry::set('Zend_Config', $this->config);
    }

    /**
     * Initialize Zf_Orm.
     * 
     * @return void
     */
    public function initOrm()
    {
        $connection = include APPLICATION_PATH . '/config/database.php';
        $dataSource = new Zf_Orm_DataSource($connection);
        
        $manager = Zf_Orm_Manager::getInstance();
        $manager->setDataSource($dataSource);
        $manager->getObjectFactory()
            ->setEntityDir(APPLICATION_PATH . '/domain/entities')
            ->setRepositoryDir(APPLICATION_PATH . '/domain/repositories');
    }

    /**
     * Initialize Zend_Log.
     * 
     * @return void
     * @throws Zf_Application_Exception
     */
    public function initLog()
    {        
        if (APPLICATION_ENV == self::ENV_LOCAL) {
            $writer = new Zend_Log_Writer_Firebug();
        } else {
            $dataSource = Zf_Orm_Manager::getInstance()->getDataSource();
            if (null === $dataSource) {
                throw new Zf_Application_Exception(sprintf('%s: Zf_Orm_DataSource is not defined.', __METHOD__));
            }
            $connection = $dataSource->getConnection(Zf_Orm_DataSource::SUPPLIER_SERVER);
            $mapper = array('priority' => 'priority', 'priority_name' => 'priorityName', 'message' => 'message');
            $writer = new Zend_Log_Writer_Db($connection, 'log', $mapper);
        }
        Zend_Registry::set('Zend_Log', new Zend_Log($writer));
    }

    /**
     * Initialize Zend_Cache.
     * 
     * @return void
     * @throws Zf_Application_Exception
     */
    public function initCache()
    {
        if (!isset($this->config->cache)) {
            throw new Zf_Application_Exception(sprintf('%s: Configuration values not defined.', __METHOD__));
        }
        $cache = Zend_Cache::factory($this->config->cache->frontend, $this->config->cache->backend);
        Zend_Registry::set('Zend_Cache', $cache);
    }
    
    /**
     * Initialize Zend_Session.
     * 
     * @return void
     */
    public function initSession()
    {
        if (self::ENV_TEST == APPLICATION_ENV) {
            Zend_Session::$_unitTestEnabled = true;
            Zend_Session::start();
        }
        $session = new Zend_Session_Namespace('Default');
        Zend_Registry::set('Zend_Session', $session);
    }

    /**
     * Initialize Zend_Router
     * 
     * @return void
     */
    public function initRouter()
    {
        $urlPatterns = include APPLICATION_PATH . '/config/routes.php';
        $routes = array();
        if (!$this->isCli) {
            if (isset($urlPatterns['default'])) {
                $routes['default'] = $urlPatterns['default'];
                unset($urlPatterns['default']);
            }
            $path = ltrim($_SERVER['REQUEST_URI'], '/') . '/';
            $route = substr($path, 0, strpos($path, '/'));
            if (isset($urlPatterns[$route]) && is_array($urlPatterns[$route])) {
                foreach ($urlPatterns[$route] as $key => $pattern) {
                    $routes[$route . '_' . $key] = $pattern;
                }
            } elseif (isset($urlPatterns[$route])) {
                $routes[$route] = $urlPatterns[$route];
            }
        } else {
            foreach ($urlPatterns as $key => $pattern) {
                if (is_array($pattern)) {
                    $routes = array_merge($routes, array_values($pattern));
                } else {
                    $routes[] = $pattern;
                }
            }
        }
        $this->getFrontController()->getRouter()->addRoutes($routes);
    }

    /**
     * Initialize Zend_Layout.
     * 
     * @return void
     */
    public function initLayout()
    {
        if (!$this->isCli) {
            $layout = Zend_Layout::startMvc();
            $layout->setLayout('main');
            $layout->setLayoutPath(APPLICATION_PATH . '/views/layouts');
            $layout->setContentKey('content');
        }
    }
    
    /**
     * Initialize controller directories
     * 
     * @return void
     */
    public function initControllers()
    {
        $this->getFrontController()->setControllerDirectory(array('default' => APPLICATION_PATH . '/controllers'));
    }
}
