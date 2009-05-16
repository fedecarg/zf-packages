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
class Zf_Controller_Action_Helper_Layout
{
    /**
     * @var string|null
     */
    protected $_script = null;
    
    /**
     * @var string|null
     */
    protected $_path = null;
    
    /**
     * @var stdClass
     */
    protected $_placeholder = null;
    
    /**
     * Class constructor.
     *
     * @param string $file
     * @param string $path
     * @return void
     */
    public function __construct($script, $path)
    {
        $this->setPath($path);
        $this->setScript($script);
    }
    
    /**
     * Set script to use.
     * 
     * @param string $path 
     * @return void
     */ 
    public function setScript($script) 
    {
        $this->_script = $script;
    }
 
    /**
     * Get current script.
     * 
     * @return string
     */ 
    public function getScript() 
    {
        return $this->_script;
    }
    
    /**
     * Set script path.
     * 
     * @param string $path 
     * @return void
     */ 
    public function setPath($path) 
    {
        if (!is_dir($path)) {
            throw new Zf_Controller_Action_Exception('No such directory: ' . $path);
        }
        $this->_path = $path;
    }
 
    /**
     * Get current script path.
     * 
     * @return string
     */ 
    public function getPath() 
    {
        return $this->_path;
    }
    
    /**
     * Set placeholder object.
     * 
     * @param stdClass 
     * @return void
     */ 
    public function setPlaceholder(stdClass $object) 
    {
        $this->_placeholder = $object;
    }
 
    /**
     * Get placeholder object.
     * 
     * @return stdClass|null
     */ 
    public function getPlaceholder() 
    {
        if (null === $this->_placeholder) {
            $this->setPlaceholder(new stdClass());
        }
        return $this->_placeholder;
    }
    
    /**
     * Create setter and getter methods.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws Zf_Controller_Action_Exception
     */
    public function __call($method, $args)
    {        
        if (isset($args[0])) {
            $this->getPlaceholder()->$method = $args[0];
            return;
        } elseif (isset($this->getPlaceholder()->$method)) {
            return $this->getPlaceholder()->$method;
        }
        
        $message = 'Invalid method call: ' . get_class($this).'::'.$method.'()';
        throw new Zf_Controller_Action_Exception($message);
    }
    
    /**
     * Proxy for getPlaceholder() method.
     *
     * @return stdClass|null
     */
    public function layout()
    {
        return $this->getPlaceholder();
    }
    
    /**
     * Processes a view script and returns the output.
     *
     * @param string|null $script
     * @return string The script output.
     * @throws Zf_Controller_Action_Exception
     */
    public function render($script = null)
    {
        $path = $this->getPath();
        if (null === $script) {
            $script = $this->getScript();
        }
        
        try {
            ob_start();
            include $path . DIRECTORY_SEPARATOR . $script;
            return ob_get_clean();
        } catch (Exception $e) {
            throw new Zf_Controller_Action_Exception($e);
        }
    }
}
