<?php
namespace App\Form\Model;

class ModuleDragModel {
    // Vars
    private $positionLeft;
    private $positionCenter;
    private $positionRight;
    
    // Properties
    public function setPositionLeft($value) {
        $this->positionLeft = $value;
    }
    
    public function setPositionCenter($value) {
        $this->positionCenter = $value;
    }
    
    public function setPositionRight($value) {
        $this->positionRight = $value;
    }
    
    // ---
    
    public function getPositionLeft() {
        return $this->positionLeft;
    }
    
    public function getPositionCenter() {
        return $this->positionCenter;
    }
    
    public function getPositionRight() {
        return $this->positionRight;
    }
    
    // Functions public
    
    // Functions private
}