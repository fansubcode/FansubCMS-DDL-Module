<?php
/**
 * This class represents a DDL server
 * 
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 */
class Ddl_Model_Server
{
    /**
     * The server configuration
     * 
     * @var Zend_Config_Ini
     */
    protected $_config;
    /**
     * The identifier which was used for the server
     * 
     * @var string
     */
    protected $_identifier = null;
    /**
     * Contains a list of files, which are hosted on the server
     * 
     * @var array
     */
    protected $_files = array();
    
    /**
     * Constructs the server object
     * 
     * @param string $identifier
     * @param Zend_Config $config 
     */
    public function __construct($identifier, Zend_Config $config)
    {
        $this->_identifier = $identifier;
        $this->_config = $config;
        
        // Get the files
        $configPath = $configPath = realpath(dirname(__FILE__) . '/../data') . DIRECTORY_SEPARATOR . $identifier . '.ini'; 
        $fileConf = new Zend_Config_Ini($configPath, 'files');
        
        foreach($fileConf->ddl->files->$identifier as $group => $uris) {
            foreach($uris as $uri) {
                $this->_files[$group][] = $uri;
            }
        }
        
        ksort($this->_files);
    }
    
    /**
     * Get the identifiers of all groups used on this server
     * 
     * @return array
     */
    public function getGroups()
    {
        $groups = array();
        foreach($this->_files as $group => $null) {
            $groups[] = $group;
        }
        
        return $groups;
    }
    
    /**
     * Returns the server configuration
     * 
     * @return Zend_Config_Ini
     */
    public function getConfig()
    {
        return $this->_config;
    }
    
    /**
     * Get the server's label
     * 
     * @return string
     */
    public function getLabel()
    {
        return empty($this->_config->label) ? $this->_identifier : $this->_config->label;
    }
    
    /**
     * Get the files hosted in a specific group (or false if group is not hosted)
     * 
     * @param string $group
     * @return array|boolean
     */
    public function getFiles($group = null)
    {
        if(empty($group)) {
            return $this->_files;
        }
        
        if(!empty($this->_files[$group])) {
            return $this->_files[$group];
        }
        
        return false;
    }
    
    /**
     * Get the link for the givien URI
     * 
     * @param string $uri 
     */
    public function getLink($uri)
    {
        $urlTemplate = $this->_config->urlTemplate;
        
        $timeout = time() + $this->_config->timeout;
        
        $link = str_replace('{PATH}', $uri, $urlTemplate);
        $link = str_replace('{HASH}', $this->_generateHash($this->_getSecret($uri)), $link);
        $link = str_replace('{TIMESTAMP}', $timeout, $link);
        
        return $link;
    }
    
    /**
     * Get the filename from an uri
     * 
     * @param string $uri
     * @return string
     */
    public function getFilename($uri)
    {
        $parts = explode('/', $uri);
        
        return $parts[count($parts)-1];
    }
    
    /**
     * Generates the plain text secret for $uri
     * 
     * @param string $uri
     * @return string
     */
    protected function _getSecret($uri) {
        $template = $this->_config->secret;
        $timeout = time() + $this->_config->timeout;
        $secret = str_replace('{REMOTE_ADDR}', $_SERVER['REMOTE_ADDR'], $template);
        $secret = str_replace('{PATH}', $uri, $secret);
        $secret = str_replace('{TIMESTAMP}', $timeout, $secret);

        return $secret;
    }
    
    /**
     * Hashes the secret
     * 
     * @param string $secret 
     */
    protected function _generateHash($secret) {
        return str_replace("=", "",
                 strtr(base64_encode(md5($secret, true)), "+/", "-_"));
    }
}