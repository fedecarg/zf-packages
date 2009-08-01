<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Application
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Application
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */
class Zf_Application_Bootstrapper
{
    private $components = array(
        'Db'           => true,
        'Log'          => true,
        'Cache'        => true,
        'Session'      => true,
        'Router'       => true,
    ); 
    
    private $config = null;
    private $isCli = false;
     
    /**
     * Constructor
     * 
     * @return void
     * @throws Zf_Application_Exception
     */
    public function __construct()
    {
        if (!defined('APPLICATION_PATH')) {
            throw new Zf_Application_Exception('APPLICATION_PATH is not defined');
        } elseif (!defined('APPLICATION_ENV')) {
            throw new Zf_Application_Exception('APPLICATION_ENV is not defined');
        }
        
        $this->initErrorHandler();
        $this->initConfig();
        
        $this->isCli = (PHP_SAPI == 'cli') ? true : false;
    }
    
    /**
     * Enable component.
     *
     * @param string $component
     * @return void
     */
    public function enable($component)
    {
        if (!array_key_exists($component, $this->components)) {
            $msg = sprintf('%s: No such component: %s', __METHOD__, $component); 
            throw new Zf_Application_Exception($msg);
        }
        $this->components[$component] = true;        
    }
    
    /**
     * Disable component.
     *
     * @param string $component
     * @return void
     */
    public function disable($component)
    {
        if (!array_key_exists($component, $this->components)) {
            $msg = sprintf('%s: No such component: %s', __METHOD__, $component);
            throw new Zf_Application_Exception($msg);
        }
        $this->components[$component] = false;
    }
    
    /**
     * App initialization 
     * 
     * @return Zf_Application_Bootstrapper
     */
    public function run()
    {
    	foreach ($this->components as $component => $activate) {
            if ($activate) {
                $this->{'init'.$component}();
            }
        }
    }

    /**
     * Initialize error handler
     * 
     * @return Zf_Application_Bootstrapper
     */
    public function initErrorHandler()
    {
        if ('prd' === APPLICATION_ENV) {
            error_reporting(0);
            ini_set('display_errors', 0);
        } else {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', 1);
        }        

        date_default_timezone_set('Europe/London');
        return $this;
    }
    
    /**
     * Initialize configuration
     * 
     * @return Zf_Application_Bootstrapper
     * @throws Zf_Application_Exception
     */
    public function initConfig()
    {
        $file = APPLICATION_PATH . '/config/settings.php';
        if (!file_exists($file)) {
            throw new Zf_Application_Exception(sprintf('No such file: %s', $file));
        }
        $this->config = include_once $file;
        Zend_Registry::set('Zend_Config', new Zf_Config_Array($this->config));
        
        return $this;
    }
    
    /**
     * Initialize database
     * 
     * @return Zf_Application_Bootstrapper
     */
    public function initDb()
    {
        if (isset($this->config['db'][0])) {
            try {
                $db = Zend_Db::factory('Pdo_Mysql', $this->config['db'][0]);
                Zend_Registry::set('Zend_Db', $db);
            } catch (Exception $e) {
                throw new Zf_Application_Exception(__METHOD__ . ': ' . $e->getMessage());
            }
        }
        return $this;
    }

    /**
     * Initialize logger
     * 
     * @return Zf_Application_Bootstrapper
     */
    public function initLog()
    {
    	if (!Zend_Registry::isRegistered('Zend_Db')) {
            $msg = sprintf('%s: Zend_Db is not registered.', __METHOD__);
            throw new Zf_Application_Exception($msg);
        }
        
        $columnMapping = array('priority' => 'priority', 'priority_name' => 'priorityName', 'message' => 'message');
        $writer = new Zend_Log_Writer_Db(Zend_Registry::get('Zend_Db'), 'log', $columnMapping);
        $logger = new Zend_Log($writer);
        Zend_Registry::set('Zend_Log', $logger);
            
        return $this;
    }

    /**
     * Initialize caching
     * 
     * @return Zf_Application_Bootstrapper
     */
    public function initCache()
    {
        $config = $this->config['cache'];
        $cache = Zend_Cache::factory($config['frontend'], $config['backend']);        
        Zend_Registry::set('Zend_Cache', $cache);      
        
        return $this;
    }
    
    /**
     * Initialize session
     * 
     * @return Zf_Application_Bootstrapper
     */
    public function initSession()
    {
        $session = new Zend_Session_Namespace('Default');
        Zend_Registry::set('Zend_Session', $session);
                
        return $this;
    }

    /**
     * Initialize Router
     * 
     * @return Zf_Application_Bootstrapper
     */
    public function initRouter()
    {
        $file = APPLICATION_PATH . '/config/routes.php';
        if (!file_exists($file)) {
            return $this;
        }
        
        $router = new Zend_Controller_Router_Rewrite();
        $router->addRoutes(include_once $file);
        Zend_Registry::set('Zend_Router', $router);
        
        return $this;
    }
}
