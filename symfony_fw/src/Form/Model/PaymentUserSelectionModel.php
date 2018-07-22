<?php
namespace App\Form\Model;

class PaymentUserSelectionModel {
    // Vars
    private $userId;
    
    // Properties
    public function setUserId($value) {
        $this->userId = $value;
    }
    
    // ---
    
    public function getUserId() {
        return $this->userId;
    }
    
    // Functions public
    
    // Functions private
}