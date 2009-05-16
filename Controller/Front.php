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
class Zf_Controller_Front
{
    /**
     * @var Zend_Controller_Request_Abstract
     */
     protected $_request = null;
     
    /**
     * @var Zend_Controller_Response_Abstract
     */
     protected $_response = null;

    /**
     * @var string Controller
     */
    protected $_controller = null;
    
    /**
     * @var string Action
     */
    protected $_action = null;
    
    /**
     * @var string Request parameters
     */
    protected $_params = null;
    
    /**
     * @var string Layout script name
     */
    protected $_layoutScript = null;
    
    /**
     * @var string Layout script path
     */
    protected $_layoutPath = null;
    
    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        if (!defined('APPLICATION_PATH')) {
            throw new Zf_Controller_Exception('APPLICATION_PATH is undefined');
        }
    }
    
    /**
     * Set the request object.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function setRequest(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
    }
    
    /**
     * Return the request object.
     *
     * @return Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        if (null === $this->_request) {
            $uri = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
            print $uri . ' -- ';
            echo $_SERVER['REQUEST_URI']; die;
            $uri = $this->filter($uri, array('\s\+\/:'));
            $this->setRequest(new Zend_Controller_Request_Http($uri));
        }

        return $this->_request;
    }
    
    /**
     * Set the response object.
     *
     * @param Zend_Controller_Response_Abstract $response
     * @return void
     */
    public function setResponse(Zend_Controller_Response_Abstract $response)
    {
        $this->_response = $response;
    }
    
    /**
     * Return the response object.
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function getResponse()
    {
        if (null === $this->_response) {
            $this->setResponse(new Zend_Controller_Response_Http());
        }
        
        return $this->_response;
    }
   
    /**
     * Set the controller name.
     *
     * @param string $name
     * @return void
     */
    public function setController($name)
    {
        $this->_controller = $name;
    }
    
    /**
     * Return the controller name.
     *
     * @return string
     */
    public function getController()
    {
        if (null === $this->_controller) {
            $params = $this->getParams();
            if (!isset($params[0]) || empty($params[0])) {
                $name = 'Default';
            } else {
                $name = $this->camelize($this->filter($params[0]));
            }
            $this->setController($name);
        }
        
        return $this->_controller;
    }
    
    /**
     * Loads a controller class from a PHP file. Returns TRUE if the file 
     * exists, or FALSE otherwise.
     *
     * @return boolean 
     */
    public function loadController($name)
    {
        $file = APPLICATION_PATH 
            . DIRECTORY_SEPARATOR . 'controllers' 
            . DIRECTORY_SEPARATOR . $name . '.php';
        
        if (!file_exists($file)) {
            return false;
        }
        
        include_once $file;
        return true;
    }
    
    /**
     * Set the action name.
     *
     * @param string $name
     * @return void
     */
    public function setAction($name)
    {
        $this->_action = $name;
    }
    
    /**
     * Return the action name.
     *
     * @return string
     */
    public function getAction()
    {
        if (null === $this->_action) {
            $params = $this->getParams();
            if (!isset($params[1]) || empty($params[1])) {
                $name = 'index';
            } else {
                $name = $this->camelize($this->filter($params[1]));
                $name[0] = strtolower($name[0]);
            }
            $this->setAction($name);
        }
        return $this->_action;
    }
    
    /**
     * Set action parameters.
     *
     * @param array $params
     * @return void
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }
    
    /**
     * Return action parameters.
     *
     * @return array
     */
    public function getParams()
    {
        if (null === $this->_params) {
            $urlPath = ltrim($this->getRequest()->getRequestUri(), '/');
            $urlPath = rtrim($urlPath, '/');
            $params = explode('/', $urlPath);
            $this->setParams($params);
        }
        
        return $this->_params;
    }
    
    /**
     * Set the layout script name.
     * 
     * @param string $script 
     * @return void
     */ 
    public function setLayoutScript($script) 
    {
        $this->_layoutScript = $script;
    }
 
    /**
     * Return the layout script name.
     * 
     * @return string
     */ 
    public function getLayoutScript() 
    {
        if (null === $this->_layoutScript) {
            $this->_layoutScript = 'main.phtml';
        }
        return $this->_layoutScript;
    }
    
    /**
     * Set the layout script path.
     * 
     * @param string $path 
     * @return void
     */ 
    public function setLayoutPath($path) 
    {
        $this->_layoutPath = $path;
    }
 
    /**
     * Return the layout script path.
     * 
     * @return string
     */ 
    public function getLayoutPath() 
    {
        return $this->_layoutPath;
    }
    
    /**
     * Dispatch an HTTP request to a controller/action.
     *
     * @return void
     */
    public function dispatch()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        
        /* Set request parameters */
        $params['controller'] = $this->getController();
        $params['action'] = $this->getAction();
        $request->setParams($params);
        
        /* Load action controller */
        $controller = $this->getController() . 'Controller';
        if (!$this->loadController($controller)) {
            $e = new Zf_Controller_Exception(sprintf('Controller "%s" does not exist.', $controller));
            return $this->handleException($e);
        }
        
        $invokeArgs = array('noViewRenderer'=>true, 'params'=>$params);
        if (null !== $this->getLayoutPath()) {
            $invokeArgs['layout']['path'] = $this->getLayoutPath();
            $invokeArgs['layout']['script'] = $this->getLayoutScript();
        }
        
        try {
            /* Instantiate action controller */
            $object = new $controller($request, $response, $invokeArgs);
            $controllerParams = $this->buildControllerParams($object->getParams());
            $object->setParams($controllerParams);
        
            /* Call action method */
            $action = $this->getAction() . 'Action';
            $script = $object->$action();
            if (null === $script || !is_string($script)) {
                $message = sprintf('Action "%s" did not return a valid view script.', $action);
                throw new Zf_Controller_Exception($message);
            }
            
            /* Render view script */
            $output = $object->getView()->render($script);
            if (true === $object->isLayoutEnabled()) {
                $layout = $object->getLayout();
                $layout->layout()->content = $output;
                $output = $layout->render();
            }
            echo $output;
        } catch (Zf_Controller_Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Build controller params.
     * 
     * @param array $param
     * @return array
     */
    public function buildControllerParams($params)
    {
        $controllerParams = $this->getRequest()->getParams();
        foreach ($params as $key => $value) {
            if (null !== $this->getRequest()->getParam($key)) {
                $controllerParams[$key] = $this->getRequest()->getParam($key);
            } else {
                $controllerParams[$key] = $value;
            }
        }
        return $controllerParams;
    }
    
    /**
     * Handle exceptions.
     *
     * @param Exception 
     * @return void
     */
    public function handleException($e)
    {
        if (!$this->loadController('ErrorController')) {
            throw new Zf_Controller_Exception($e->getMessage());
        }
        
        $invokeArgs['params'] = array('error', 'index');
        $controller = new ErrorController($this->getRequest(), $this->getResponse(), $invokeArgs);
        $controller->indexAction($e);
    }
    
    /**
     * Convert a string separated by dashes or underscores into a camel-case equivalent.
     *
     * @param string $string
     * @return string
     */
    public function camelize($string)
    {
        $replace = array('-', '_', '/');
        return str_replace(' ', '', ucwords(str_replace($replace, ' ', strtolower($string))));
    }
    
    /**
     * Filter input.
     *
     * @param string $string
     * @param array|null $allowChars
     * @return string
     */
    public function filter($string, array $allowChars = null)
    {
        $chars = '';
        if (null !== $allowChars) {
            $chars = implode('', $allowChars);
        }
        return preg_replace('/[^a-z0-9-_'.$chars.']/i', '', $string);
    }
}
