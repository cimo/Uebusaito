<?php
namespace App\Form\Model;

class PaymentSelectionModel {
    // Vars
    private $id;
    
    // Properties
    public function setId($value) {
        $this->id = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
}