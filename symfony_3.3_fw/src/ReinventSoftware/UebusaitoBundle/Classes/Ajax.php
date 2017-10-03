<?php
// Version 1.0.0

namespace ReinventSoftware\UebusaitoBundle\Classes;

use Symfony\Component\HttpFoundation\JsonResponse;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;

class Ajax {
    // Vars
    private $container;
    private $entityManager;
    
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct($container, $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->utility = new Utility($this->container, $this->entityManager);
    }
    
    public function response($array) {
        return new JsonResponse($array);
    }
    
    public function errors($elements) {
        if (is_string($elements) == false) {
            $errors = Array();
            
            foreach ($elements->getErrors() as $error)
                $errors[] = $this->utility->getTranslator()->trans($error->getMessage());
            
            foreach ($elements->all() as $key => $value)
                $errors[$key] = $this->errors($value);

            return $errors;
        }
        else
            return $this->utility->getTranslator()->trans($elements);
        
        return "";
    }
    
    // Functions private
}