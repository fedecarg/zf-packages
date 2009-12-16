<?php
interface Zf_Service_ComponentInterface
{
    public function getFrontController();
    public function setServiceManager(Zf_Service_Manager $manager);
    public function getServiceManager();
}