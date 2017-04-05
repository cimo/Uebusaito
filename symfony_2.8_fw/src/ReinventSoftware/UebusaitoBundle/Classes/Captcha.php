<?php
namespace ReinventSoftware\UebusaitoBundle\Classes;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;

class Captcha {
    // Vars
    private $container;
    private $entityManager;
    
    private $utility;
    private $utilityPrivate;
    
    // Properties
    
    // Functions public
    public function __construct($container, $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
    }
    
    public function create($length) {
        $randomString = $this->utility->generateRandomString($length);
        
        $_SESSION['captcha'] = $randomString;
        
        return $this->image($randomString);
    }
    
    // Functions private
    private function image($string) {
        $image = imagecreate(100, 30);
        
        $background = imagecolorallocate($image, 0, 0, 255);
        $color = imagecolorallocate($image, 255, 255, 255);
        $line = imagecolorallocate($image, 140, 140, 140);
        
        imagestring($image, 5, 20, 10, $string, $color);
        
        imageline($image, 0, 0, 50, 30, $line);
        imageline($image, 30, 0, 80, 30, $line);
        imageline($image, 50, 0, 20, 30, $line);
        imageline($image, 80, 0, 40, 30, $line);
        
        ob_start();
            header("Content-type: image/png");
            imagepng($image);
            $result = base64_encode(ob_get_contents());
        ob_end_clean();
        
        imagecolordeallocate($image, $line);
        imagecolordeallocate($image, $color);
        imagecolordeallocate($image, $background);
        imagedestroy($image);
        
        return $result;
    }
}