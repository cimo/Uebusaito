<?php
namespace ReinventSoftware\UebusaitoBundle\Classes\System;

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
        
        return $this->image($_SESSION['captcha']);
    }
    
    // Functions private
    private function image($string) {
        $image = imagecreatetruecolor(80, 30);
        $red = imagecolorallocate($image, 0xFF, 0x00, 0x00);
        $black = imagecolorallocate($image, 0x00, 0x00, 0x00);
        
        imagefilledrectangle($image, 0, 0, 299, 99, $red);
        
        $font_file = "{$this->utility->getPathSrcBundle()}/Resources/public/fonts/Roboto_light.ttf";
        
        imagefttext($image, 10, 0, 12, 20, $black, $font_file, $string);
        
        ob_start();
            header("Content-type: image/png");
            imagepng($image);
            $result = base64_encode(ob_get_contents());
        ob_end_clean();
        
        imagedestroy($image);
        
        return $result;
    }
    
    public function check($captchaEnabled, $captcha) {
        if ($captchaEnabled == false || ($captchaEnabled == true && isset($_SESSION['captcha']) == true && $_SESSION['captcha'] == $captcha))
            return true;
        
        return false;
    }
}