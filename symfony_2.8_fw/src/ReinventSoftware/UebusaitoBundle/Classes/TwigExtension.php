<?php
namespace ReinventSoftware\UebusaitoBundle\Classes;

class TwigExtension extends \Twig_Extension {
    // Vars
    
    // Properties
    public function getFunctions() {
        return Array(
            "file_exists" => new \Twig_Function_Function("file_exists")
        );
    }
    
    public function getName() {
        return "uebusaito_file_exists";
    }
    
    // Functions public
    
    // Functions private
}