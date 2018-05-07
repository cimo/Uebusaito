<?php
// Version 1.0.0

namespace ReinventSoftware\UebusaitoBundle\Classes;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;

class Captcha {
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
    
    public function create($length) {
        $randomString = $this->utility->generateRandomString($length);
        
        $_SESSION['captcha'] = $randomString;
        
        return $this->image($randomString);
    }
    
    // Functions private
    private function image($string) {
        $image = imagecreate(70, 30);
        
        $background = imagecolorallocate($image, 0, 0, 255);
        $color = imagecolorallocate($image, 255, 255, 255);
        $line = imagecolorallocate($image, 140, 140, 140);
        
        imagestring($image, 5, 5, 7, $string, $color);
        
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
    
    public function check($captchaEnabled, $captcha) {
        if ($captchaEnabled == false || ($captchaEnabled == true && isset($_SESSION['captcha']) == true && $_SESSION['captcha'] == $captcha))
            return true;
        
        return false;
    }
}