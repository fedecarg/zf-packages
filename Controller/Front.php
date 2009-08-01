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
class Zf_Controller_Front
{
    protected $_request = null;
    protected $_controller = null;
    protected $_action = null;
    protected $_layoutScript = null;
    protected $_layoutPath = null;
    
    public function setRequest(Zf_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
    }
    
    public function getRequest()
    {
        if (null === $this->_request) {
            $request = new Zf_Controller_Request_Http();
            $this->setRequest(new Zf_Controller_Request_Http($request));
        }

        return $this->_request;
    }
    
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
    
    public function getParams()
    {
	    $urlPath = ltrim($this->getRequest()->getRequestUri(), '/');
	    $urlPath = rtrim($urlPath, '/');
	    $parts = explode('/', $urlPath);
	   
	    /* Controller */
	    $controller = 'Index';
        if (isset($parts[0]) && !empty($parts[0])) {
            $controller = $this->camelize($this->filter($parts[0]));
        }
        
        /* Action */
        $action = 'index';
        if (isset($parts[1]) && !empty($parts[1])) {
            $action = $this->camelize($this->filter($parts[1]));
            $action[0] = strtolower($action[0]);
        }
        
        /* Parameters */
        $params = array();
        foreach (array_slice($parts, 2) as $key => $param) {
            $params[] = $param;
        }
	    
	    return array(
	        'controller' => $controller, 
	        'action'     => $action,
	        'params'     => $params,
	    );
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
        if (null === $this->_layoutScript) {
            $this->_layoutScript = 'main.phtml';
        }
        return $this->_layoutScript;
    }
    
    /**
     * Set layout path.
     * 
     * @param string $path 
     * @return void
     */ 
    public function setLayoutPath($path) 
    {
        $this->_layoutPath = $path;
    }
 
    /**
     * Return layout path.
     * 
     * @return string
     */ 
    public function getLayoutPath() 
    {
        return $this->_layoutPath;
    }
    
    public function dispatch()
    {
        $request = $this->getRequest();
        $request->setParams($this->getParams());
        
        if (Zend_Registry::isRegistered('Zend_Router')) {
            $router = Zend_Registry::get('Zend_Router');
            $router->setParams($request->getParams());
            $router->route($request);
        }
        
        $invokeArgs = array('noViewRenderer' => true, 'dispatch' => $request->getParams());
        if (null !== $this->getLayoutPath()) {
            $invokeArgs['layout']['path'] = $this->getLayoutPath();
            $invokeArgs['layout']['script'] = $this->getLayoutScript();
        }
        
        try {
	        /* Load action controller */
        	$name = ucfirst(strtolower($request->getParam('controller', 'Index'))) . 'Controller';
        	$controller = $this->getController($name, $invokeArgs);
        	$controller->init();
        	
        	/* Execute action method */
        	$action = $request->getParam('action', 'index');
            $script = $controller->{$action.'Action'}();
            if (null === $script || !is_string($script)) {
	            $message = sprintf('Action "%s" did not return a valid view script.', $action);
	            throw new Zf_Controller_Exception($message);
            }
            
            /* Render view script */
            $this->render($script, $controller);
        } catch (Exception $e) {
            $this->handleException($e, $invokeArgs);
        }
    }
        
    public function getController($name, $invokeArgs = array())
    {    	
	    /* Load action controller */
	    if (!$this->loadController($name)) {
	        throw new Zf_Controller_Exception(sprintf('Controller "%s" does not exist.', $name));
	    }
	
	    /* Instantiate action controller */
	    $request = $this->getRequest();
	    $controller = new $name($request, $invokeArgs);
	    $params = $this->buildControllerParams($controller->getParams());
	    $controller->setParams($params);
	    
	    /* Define action method */
	    $action = $request->getParam('action', 'index');
	    if ('index' === $action && 'index' !== $controller->getDefaultAction()) {
	        $params['action'] = $controller->getDefaultAction();
	        $controller->setParams($params);
	        $request->setParam('action', $controller->getDefaultAction());
	    }

	    return $controller;
    }
    
    public function buildControllerParams($controllerParams)
    { 
    	$requestArgs = $this->getRequest()->getParams();
    	$params = array_slice($requestArgs, 0, 2);
    	
        $i = 0;
        foreach ($controllerParams as $key => $value) {
        	if ($this->getRequest()->has($key)) {
                $params[$key] = $this->getRequest()->getParam($key);
            } elseif (array_key_exists($i, $requestArgs['params'])) {
                $params[$key] = $requestArgs['params'][$i];
            } else {
                $params[$key] = $value;
            }
            $i++;
        }
        
        return $params;
    }
    
    public function render($script, Zf_Controller_Action $controller)
    {
        $output = $controller->getView()->render($script);
        if (true === $controller->isLayoutEnabled()) {
            $layout = $controller->getLayout();
            $layout->layout()->currentAction = $this->getRequest()->getParam('action');
            $layout->layout()->content = $output;
            $output = $layout->render();
        }
        echo $output;
    }
    
    
    public function handleException($e, $args)
    {
        if (!$this->loadController('ErrorController')) {
            throw new Zf_Controller_Exception($e->getMessage());
        }
        
        $dispatch = array('controller' => 'error', 'action' => 'index');
        $this->getRequest()->setParam('dispatch', $dispatch);
        
        $controller = $this->getController('ErrorController', $args);
        $controller->setParams($dispatch);
        $script = $controller->indexAction($e);
        $this->render($script, $controller);
    }
    
    public function camelize($string)
    {
        return str_replace(' ', '', ucwords(str_replace(array('-', '_', '/'), ' ', strtolower($string))));
    }
    
    /**
     * Filter input.
     *
     * @param string $string
     * @param boolean $allowChars
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
