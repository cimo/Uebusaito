<?php
namespace App\Classes\System;

class QueryCustom {
    // Vars
    private $helper;
    
    private $connection;
    
    // Properties
      
    // Functions public
    public function __construct($helper) {
        $this->helper = $helper;
        
        $this->connection = $this->helper->getConnection();
    }
    
    // Functions private
}