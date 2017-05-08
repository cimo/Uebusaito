<?php
namespace ReinventSoftware\UebusaitoBundle;

class Config {
    // Vars
    private $pathRoot;
    private $urlRoot;
    private $file;
    private $databaseConnectionFields;
    private $name;
    
    // Properties
    public function getDatabaseConnectionFields() {
        return $this->databaseConnectionFields;
    }
    
    public function getPathRoot() {
        return $this->pathRoot;
    }
    
    public function getUrlRoot() {
        return $this->urlRoot;
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
        $this->pathRoot = "/uebusaito/symfony_2.8_fw";
        $this->urlRoot = "/uebusaito/symfony_2.8_fw/web";
        $this->file = "/app_dev.php";
        $this->name = "Uebusaito 1.0";
    }
    
    // Functions private
}