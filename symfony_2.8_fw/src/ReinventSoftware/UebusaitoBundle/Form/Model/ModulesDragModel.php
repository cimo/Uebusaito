<?php
namespace ReinventSoftware\UebusaitoBundle\Form\Model;

class ModulesDragModel {
    // Vars
    private $sortHeader;
    private $sortLeft;
    private $sortCenter;
    private $sortRight;
    
    // Properties
    public function setSortHeader($value) {
        $this->sortHeader = $value;
    }
    
    public function setSortLeft($value) {
        $this->sortLeft = $value;
    }
    
    public function setSortCenter($value) {
        $this->sortCenter = $value;
    }
    
    public function setSortRight($value) {
        $this->sortRight = $value;
    }
    // ---
    public function getSortHeader() {
        return $this->sortHeader;
    }
    
    public function getSortLeft() {
        return $this->sortLeft;
    }
    
    public function getSortCenter() {
        return $this->sortCenter;
    }
    
    public function getSortRight() {
        return $this->sortRight;
    }
    
    // Functions public
    
    // Functions private
}