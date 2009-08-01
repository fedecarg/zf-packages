<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Controller
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Controller
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */
abstract class Zf_Controller_Action
{
    /**
     * @var array URL parameters
     */
    protected $_params = array();
	
    /**
     * @var array Arguments provided to the constructor.
     */
    protected $_invokeArgs = array();
    
    /**
     * @var Zf_Controller_Request_Abstract
     */
    protected $_request = null;

    /**
     * @var array Object container.
     */
    protected $_container = array();
    
    /**
     * @var Zf_Controller_Action_Helper_Layout
     */
    protected $_layout = null;

    /**
     * @var boolean
     */
    protected $_layoutScript = null;
        
    /**
     * @var boolean
     */
    protected $_layoutEnabled = true;
    
    /**
     * @var Zend_View_Interface
     */
    protected $_view = null;
    
    /**
     * @var string URL path
     */
    protected $_urlPath = null;
    
    /**
     * @var string Default action name
     */
    protected $_defaultAction = 'index';
    
    /**
     * Class constructor.
     *
     * @param Zf_Controller_Request_Abstract $request
     * @param Zf_Controller_Response_Abstract $response
     * @param array $params
     * @param array $args
     */
    public function __construct(Zf_Controller_Request_Abstract $request, array $args = array())
    {
        $this->setRequest($request);
        $this->setInvokeArgs($args);
    }
    
    /**
     * Initialize object
     *
     * @return void
     */
    public function init()
    {}
    
    /**
     * Set the Request object.
     *
     * @param Zf_Controller_Request_Abstract $request
     * @return void
     */
    public function setRequest(Zf_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
    }
    
    /**
     * Return the Request object.
     *
     * @return Zf_Controller_Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * Set resource.
     * 
     * @param object $object
     * @param string $id
     * @return void
     */
    public function setResource($object, $id)
    {
        Zend_Registry::set($id, $object);
    }
    
    /**
     * Return resource.
     * 
     * @param string $name Resource name
     * @param string $class Class name
     * @param string $directory Resource directory: services, models, etc.
     * @return object|boolean Resource or false
     */
    public function getResource($name, $class, $directory)
    {
        $id = 'Resource_' . $class;
        if (!Zend_Registry::isRegistered($id)) {
            require_once APPLICATION_PATH . '/'. $directory . '/' . $class . '.php';
            $instance = new $class;
            if ($instance instanceof Zf_Model_Abstract) {
                $instance->setModelName($name);
            }
            $this->setResource($instance, $id); 
        }
        return Zend_Registry::get($id); 
    }
    
    /**
     * Return model.
     * 
     * @param string $name
     * @return object Model
     */
    public function getModel($name)
    {
        $class = $name . 'Model';
        return $this->getResource($name, $class, 'models');
    }
    
    /**
     * Return service.
     * 
     * @param string $name
     * @return object Service
     */
    public function getService($name)
    {
        $class = $name . 'Service';
        return $this->getResource($name, $class, 'services');
    }
    
    /**
     * Return the default action name.
     *
     * @return string
     */
    public function getDefaultAction()
    {
        return $this->_defaultAction;
    }
    
    /**
     * Set invocation arguments.
     *
     * @param array $args
     * @return void
     */
    public function setInvokeArgs(array $args = array())
    {
        $this->_invokeArgs = $args;
    }

    /**
     * Return the array of constructor arguments.
     *
     * @return array
     */
    public function getInvokeArgs()
    {
        return $this->_invokeArgs;
    }

    /**
     * Return a single invocation argument
     *
     * @param string $key
     * @return mixed
     */
    public function getInvokeArg($key)
    {
        if (!isset($this->_invokeArgs[$key])) {
            return null;
        }
        return $this->_invokeArgs[$key];
    }
        
    /**
     * Return parameter.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function _getParam($name, $default = null)
    {
        if (!array_key_exists($name, $this->_params)) {
            return $default;
        }
        return $this->_params[$name];
    }
    
    /**
     * Set parameter.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function _setParam($name, $value)
    {
        $this->_params[$name] = $value;
    }
    
    /**
     * Determine whether a given URL parameter exists.
     *
     * @param string $name
     * @return boolean
     */
    protected function _hasParam($name)
    {
        return array_key_exists($name, $this->_params);
    }
    
    /**
     * Sets the paramteres of the controller.
     *
     * @param array $params
     * @return void
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }
    
    /**
     * Return the parameters of the controller.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }
    
    /**
     * Set URL path.
     *
     * @param string $url
     * @return void
     */
    public function setBaseUrl($url)
    {
        $this->_urlPath = $url;
    }

    /**
     * Return the URL path.
     *
     * @return array
     */
    public function getBaseUrl()
    {
    	if (null === $this->_urlPath) {
	        $path = strtolower('/' . ltrim(implode('/', $this->getParams()), '/'));
	        $this->setBaseUrl($path);
    	}
        return $this->_urlPath;
    }
    
    /**
     * Set a single instance of a given object.
     * 
     * @param object $object
     * @param string $name
     * @return void
     */
    public function setInstance($object)
    {
        $name = get_class($object);
        $this->_container[$name] = $object;
    }
    
    /**
     * Get instance of class.
     * 
     * @param string $name
     * @return object|false
     */
    public function getInstance($name)
    {
        if (!$this->hasInstance($name)) {
            $object = new $name();
            $this->setInstance($object, $name); 
        }
        return $this->_container[$name]; 
    }
    
    /**
     * Determine whether a given object exists. 
     * 
     * @param string $name
     * @return boolean
     */
    public function hasInstance($name)
    {
        return array_key_exists($name, $this->_container);
    }
    
    /**
     * Set layout object.
     * 
     * @param Zf_Controller_Action_Helper_Layout 
     * @return void
     */ 
    public function setLayout(Zf_Controller_Action_Helper_Layout $object) 
    {
        $this->_layout = $object;
    }
 
    /**
     * Get layout object.
     * 
     * @return Zf_Controller_Action_Helper_Layout
     * @throws Zf_Controller_Exception
     */ 
    public function getLayout() 
    {
        if (null === $this->_layout) {
            if (!$this->isLayoutEnabled()) {
                throw new Zf_Controller_Exception('Layout is disabled or undefined.');
            }
            $args = $this->getInvokeArg('layout');
            $script = $args['script'];
            if (null !== $this->getLayoutScript()) {
                $script = $this->getLayoutScript();
            }
            $layout = new Zf_Controller_Action_Helper_Layout($script, $args['path']);
            $this->setLayout($layout);
        }
        return $this->_layout;
    }
    
    /**
     * Set layout script.
     * 
     * @param string $script 
     * @return void
     */ 
    public function setLayoutScript($script) 
    {
        $this->_layoutScript = $script;
    }
 
    /**
     * Get layout script.
     * 
     * @return string
     */ 
    public function getLayoutScript() 
    {
        return $this->_layoutScript;
    }
    
    /**
     * Determine whether the layout is enabled.
     *
     * @return unknown
     */
    public function isLayoutEnabled()
    {
        return (null !== $this->getInvokeArg('layout') && true === $this->_layoutEnabled); 
    }
    
    /**
     * Set the Zend_View object.
     *
     * @param $view Zend_View_Interface
     * @return void
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->_view = $view;
    }
    
    /**
     * Return the Zend_View object.
     *
     * @return Zend_View_Interface
     */
    public function getView()
    {
        if (null === $this->_view) {
            $baseDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'views';
            $this->setView(new Zend_View(array('basePath' => $baseDir)));
        }
        return $this->_view;
    }
    
    /**
     * Return a new instance of Zend_View.
     *
     * @return Zend_View_Interface
     */
    public function initView()
    {
        $this->_view = null;
        return $this->getView();
    }
    
    /**
     * Construct view script path. Used by render() to determine the path 
     * to the view script.
     *
     * @param string $action Defaults to action registered in request object
     * @return string
     */
    public function getViewScript($name)
    {
        $controller = strtolower($this->_getParam('controller', 'index'));
        return $controller . DIRECTORY_SEPARATOR . $name . '.phtml';
    }
    
    /**
     * Render a script.
     *
     * @param string|null $name Script name
     * @return string
     */
    public function render($name = null)
    {
        if (null === $name) {
            $name = $this->_getParam('action', 'index');
        }
        return $this->getViewScript($name);
    }
        
    /**
     * Redirect to another URI.
     *
     * @param string $uri
     * @param integer $code HTTP status codes
     * @return void
     */
    protected function _redirect($uri, $code = 302)
    {
    	$this->getRequest()->redirect($uri, $code);
    }
    
    /**
     * Return the Session object.
     *
     * @return Zend_Session_Abstract
     * @throws Zf_Controller_Action_Exception
     */
    public function getSession()
    {
    	if (!Zend_Registry::isRegistered('Zend_Session')) {
            throw new Zf_Controller_Action_Exception('Zend_Session is undefined');
        }
        return Zend_Registry::get('Zend_Session');
    }
    
    /**
     * Return the Config object.
     *
     * @return Zf_Config_Array
     * @throws Zf_Controller_Action_Exception
     */
    public function getConfig()
    {
        if (!Zend_Registry::isRegistered('Zend_Config')) {
            throw new Zf_Controller_Action_Exception('Zend_Config is undefined');
        }
        return Zend_Registry::get('Zend_Config');
    }
    
    /**
     * Proxy for undefined methods.
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {        
        if ('Action' == substr($method, -6)) {
            $action = substr($method, 0, strlen($method) - 6);
            $message = sprintf('Action "%s" does not exist.', $method);
            throw new Zf_Controller_Action_Exception($message, 404);
        }
        
        $message = sprintf('Method "%s" does not exist.', $method);
        throw new Zf_Controller_Action_Exception($message, 500);
    }
}
