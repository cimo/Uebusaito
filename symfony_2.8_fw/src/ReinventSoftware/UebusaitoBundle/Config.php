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
    public function getPathRoot() {
        return $this->pathRoot;
    }
    
    public function getUrlRoot() {
        return $this->urlRoot;
    }
    
    public function getFile() {
        return $this->file;
    }
    
    public function getDatabaseConnectionFields() {
        return $this->databaseConnectionFields;
    }
    
    public function getName() {
        return $this->name;
    }
    
    // Functions public
    public function __construct() {
        $this->pathRoot = "/uebusaito/symfony_2.8_fw";
        $this->urlRoot = "/uebusaito/symfony_2.8_fw/web";
        $this->file = "/app_dev.php";
        $this->databaseConnectionFields = Array("", "", "");
        $this->name = "Uebusaito 1.0";
    }
    
    // Functions private
}