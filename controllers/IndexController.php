<?php
class Ddl_IndexController extends FansubCMS_Controller_Action
{
    /**
     *
     * @var Zend_Config
     */
    public $config;
    
    public function init()
    {
        $configPath = realpath(dirname(__FILE__) . '/../configs') . DIRECTORY_SEPARATOR . 'module.ini';
        $this->config = new Zend_Config_Ini($configPath, 'settings');
    }
    
    public function indexAction()
    {
        $groups = array();
        if(!empty($this->config->ddl->groups)) {
            foreach($this->config->ddl->groups as $identifier => $config) {
                $groups[$identifier] = $config->label;
            }
        }
        
        $servers = array();
        if(!empty($this->config->ddl->servers)) {
            foreach($this->config->ddl->servers as $identifier => $config) {
                $servers[$identifier] = new Ddl_Model_Server($identifier, $config);
            }
        }
        
        $this->view->servers = $servers;
        $this->view->groups = $groups;
    }
}