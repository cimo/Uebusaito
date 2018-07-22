<?php
namespace App\Form\Model;

class LanguageModel {
    // Vars
    private $codeText;
    private $codePage;
    
    // Properties
    public function setCodeText($value) {
        $this->codeText = $value;
    }
    
    public function setCodePage($value) {
        $this->codePage = $value;
    }
    
    // ---
    
    public function getCodeText() {
        return $this->codeText;
    }
    
    public function getCodePage() {
        return $this->codePage;
    }
    
    // Functions public
    
    // Functions private
}