<?php
namespace ReinventSoftware\UebusaitoBundle\Classes;

use Symfony\Component\HttpFoundation\JsonResponse;

class Ajax {
    // Vars
    private $translator;
    
    // Properties
    
    // Functions public
    public function __construct($translator) {
        $this->translator = $translator;
    }
    
    public function response($array) {
        return new JsonResponse($array);
    }
    
    public function errors($elements) {
        if (is_string($elements) == false) {
            $errors = Array();
            
            foreach ($elements->getErrors() as $error)
                $errors[] = $this->translator->trans($error->getMessage());
            
            foreach ($elements->all() as $key => $value)
                $errors[$key] = $this->errors($value);

            return $errors;
        }
        else
            return $this->translator->trans($elements);
        
        return "";
    }
    
    // Functions private
}