<?php
namespace App;

class Config {
    // Vars
    private $databaseConnectionFields;
    private $protocol;
    private $pathRoot;
    private $urlRoot;
    private $supportSymlink;
    private $file;
    private $name;
    private $curlLogin;
    
    // Properties
    public function getDatabaseConnectionFields() {
        return $this->databaseConnectionFields;
    }
    
    public function getProtocol() {
        return $this->protocol;
    }
    
    public function getPathRoot() {
        return $this->pathRoot;
    }
    
    public function getUrlRoot() {
        return $this->urlRoot;
    }
    
    public function getSupportSymlink() {
        return $this->supportSymlink;
    }
    
    public function getFile() {
        return $this->file;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getCurlLogin() {
        return $this->curlLogin;
    }
    
    // Functions public
    public function __construct() {
        $this->databaseConnectionFields = Array("", "", "", Array());
        $this->protocol = isset($_SERVER['HTTPS']) == true ? "https://" : "http://";
        $this->pathRoot = "/projects/uebusaito/symfony_fw";
        $this->urlRoot = "/projects/uebusaito/symfony_fw/public";
        $this->supportSymlink = true;
        $this->file = "/index.php";
        $this->name = "Uebusaito 1.0.0";
        $this->curlLogin = Array("", "", "");
    }
    
    // Functions private
}