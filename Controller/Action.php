<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Controller
 * @author      Federico Cargnelutti <fedecarg@yahoo.co.uk>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Controller
 * @author      Federico Cargnelutti <fedecarg@yahoo.co.uk>
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
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request = null;

    /**
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response = null;
    
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
     * Class constructor.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $args
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $args = array())
    {
        $this->setRequest($request);
        $this->setResponse($response);
        $this->setInvokeArgs($args);
             
        $this->init();
    }
    
    /**
     * Initialize object
     *
     * @return void
     */
    public function init()
    {}
    
    /**
     * Return the Request object.
     *
     * @return Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set the Request object.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function setRequest(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
    }

    /**
     * Return the Response object.
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Set the Response object.
     *
     * @param Zend_Controller_Response_Abstract $response
     * @return void
     */
    public function setResponse(Zend_Controller_Response_Abstract $response)
    {
        $this->_response = $response;
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
     * Sets all parameters as an associative array.
     *
     * @param array $params
     * @return void
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }
    
    /**
     * Return all parameters as an associative array.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }
    
    /**
     * Return a URL parameter.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (!array_key_exists($name, $this->_params)) {
            return $default;
        }
        return $this->_params[$name];
    }
    
    /**
     * Set a URL parameter.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setParam($name, $value)
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
     * Render a script.
     *
     * @param string|null $name Script name
     * @return string
     */
    public function render($name = null)
    {
        if (null === $name) {
            $name = $this->getParam('action', 'index');
        }
        return $this->getViewScript($name);
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
        $controller = $this->getParam('controller', null);
        if (null === $controller) {
            $controller = 'default';
        }
        $script = strtolower($controller) . DIRECTORY_SEPARATOR . $name . '.phtml';
        return $script;
    }
        
    /**
     * Redirect to another URL.
     *
     * @param string $url
     * @param integer $code HTTP status codes
     * @return void
     */
    public function redirect($url, $code = 302)
    {
        $this->getResponse()->setRedirect($url, $code)->sendHeaders();
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
            $message = sprintf('Action "%s" does not exist and was not trapped in __call()', $action);
            throw new Zf_Controller_Action_Exception($message, 404);
        }
        
        $message = sprintf('Method "%s" does not exist and was not trapped in __call()', $method);
        throw new Zf_Controller_Action_Exception($message, 500);
    }
    
            
    /**
     * Filter user input.
     *
     * @param string $input
     * @return string
     */
    public function filterInput($input, $allowSpaces = true)
    {
        $whiteSpace = $allowSpaces ? '\s' : '';
        return preg_replace('/[^a-z0-9-_'.$whiteSpace.']/i', '', $input);
    }
}