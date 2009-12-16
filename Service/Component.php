<?php
class Zf_Service_Component implements Zf_Service_ComponentInterface
{
    protected $_serviceManager;
    protected $_dataMapper;
        
    public function setServiceManager(Zf_Service_Manager $manager) {}
    public function getServiceManager() {}
    public function setDataMapper(Zf_DataSource_Dao_DataMapper $mapper) {}
    public function getDataMapper($name) {}
    public function getFrontController() {}
    public function getRequest() {}
}