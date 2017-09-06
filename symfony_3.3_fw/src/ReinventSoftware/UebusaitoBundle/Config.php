<?php
namespace ReinventSoftware\UebusaitoBundle;

class Config {
    // Vars
    private $databaseConnectionFields;
    private $protocol;
    private $pathRoot;
    private $urlRoot;
    private $supportSymlink;
    private $file;
    private $name;
    
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
    
    // Functions public
    public function __construct() {
        $this->databaseConnectionFields = Array("", "", "");
        $this->protocol = isset($_SERVER['HTTPS']) == true ? "https://" : "http://";
        $this->pathRoot = "/uebusaito/symfony_3.3_fw";
        $this->urlRoot = "/uebusaito/symfony_3.3_fw/web";
        $this->supportSymlink = true;
        $this->file = "/app_dev.php";
        $this->name = "Uebusaito 1.0.0";
    }
    
    // Functions private
}