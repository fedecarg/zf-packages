<?php
/**
 * Zf_Feed custom exception
 */
class Zf_Feed_Exception extends Zend_Exception {}

/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Feed
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Feed
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */
class Zf_Feed_Rss
{
    /**
     * @param string $uri
     * @params array|null $params
     * @return Zf_Http_Client
     * @throws Zf_Feed_Exception
     */
    public function import($uri, $params = null)
    {
        if (is_null($uri) || empty($uri)) {
            throw new Zf_Feed_Exception('Invalid URI');
        }
        try {
            $httpClient = Zf_Http_Client::get($uri, $params);
        } catch (Exception $e) {
            throw new Zf_Feed_Exception($e->getMessage());
        }
        return $httpClient;
    }
    
    /**
     * Returns an array of SimpleXMLElement objects representing data.
     * 
     * @param string $xml
     * @return array
     */
    public function createSimpleXmlElement($xml)
    {
        if (!$this->isValidXmlString($xml)) {
            throw new Zf_Feed_Exception('Unable to load XML string: Invalid XML document syntax');
        }
        // Represent an element in XML document and register namespaces 
        $simpleXml = new SimpleXmlElement($xml);
        $namespaces = $simpleXml->getNamespaces(true);
        foreach ($namespaces as $name => $url) {
            $simpleXml->registerXPathNamespace($name, $url);
        }
        // Find the "item" channel element  
        if (isset($simpleXml->channel->item)) {
            // RSS 1, 2 and Media RSS
            $items = $simpleXml->channel->item;
        } elseif (isset($simpleXml->item)) {
            // Custom RSS
            $items = $simpleXml->item;
        } else {
            // Invalid RSS
            throw new Zf_Feed_Exception('Channel element "item" not found');
        }
        return $items;
    }
    
    /**
     * @param string $string
     * @return boolean
     */
    public function isValidXmlString($string)
    {
        return (substr(trim($string), 2, 3) == 'xml' || substr(trim($string), 1, 3) == 'rss');
    }
    
    /**
     * @param SimpleXMLElement $element
     * @return array
     */
    public function extractNodeNames(SimpleXMLElement $element)
    {
        $vars = get_object_vars($element);
        unset($vars['@attributes']);
        return array_keys($vars);
    }
}