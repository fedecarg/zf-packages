<?php
class Zf_Service_Manager
{
    protected $_serviceFactory;
    protected $_namespace;
    protected $_config = array();
    protected $_services = array();
        
    public function setConfig(array $config) {}
    public function getConfig() {}
    
    public function setNamespace($namespace) {}
    public function getNamespace() {}
    
    public function setServiceFactory(Zf_Service_Factory $factory)
    public function getServiceFactory() {}

    public function setService($name, Zf_Service_ComponentInterface $object) {}
    public function getService($name) {}
    public function hasService($name) {}
    public function createService($name) {}
}