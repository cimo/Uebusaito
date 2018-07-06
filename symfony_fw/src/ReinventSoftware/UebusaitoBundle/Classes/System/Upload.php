<?php
namespace ReinventSoftware\UebusaitoBundle\Classes\System;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;

class Upload {
    // Vars
    private $container;
    private $entityManager;
    
    private $utility;
    
    private $settings;
    
    private $tmp;
    private $name;
    
    // Properties
    public function setSettings($value) {
        $this->settings = $value;
    }
    
    // Functions public
    public function __construct($container, $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        
        $this->settings = Array();
        
        $this->tmp = "0";
        $this->name = "";
    }
    
    public function processFile() {
        $action = "";
        
        if (isset($_GET['action']) == true)
            $action = $_GET['action'];
        
        if (isset($_GET['tmp']) == true)
            $this->tmp = $_GET['tmp'];
        
        if (isset($_GET['name']) == true)
            $this->name = $_GET['name'];
        
        if ($action == "change")
            return $this->change();
        else if ($action == "start")
            return $this->start();
        else if ($action == "finish")
            return $this->finish();
        else if ($action == "abort")
            return $this->abort();
    }
    
    // Functions private
    private function change() {
        foreach($this->settings['paths'] as $key => $value) {
            $imageSize = getimagesize($_FILES["file"]["tmp_name"]);
            $fileSize = $_FILES["file"]["size"];
            $fileName = basename($_FILES["file"]["name"]);
            
            if ($imageSize !== false) {
                if ($imageSize[0] > $this->settings['imageWidth']  ||  $imageSize[1] > $this->settings['imageHeight']) {
                    return Array(
                        'status' => 1,
                        'text' => $this->utility->getTranslator()->trans("classUpload_3") . "{$this->settings['imageWidth']} px - {$this->settings['imageHeight']} px."
                    );
                }
            }
            
            if ($fileSize > $this->settings['maxSize']) {
                return Array(
                    'status' => 1,
                    'text' => $this->utility->getTranslator()->trans("classUpload_1") . $this->utility->sizeUnits($this->settings['maxSize']) . "."
                );
            }
            
            if (in_array(mime_content_type($_FILES["file"]["tmp_name"]), $this->settings['types']) == false) {
                return Array(
                    'status' => 1,
                    'text' => $this->utility->getTranslator()->trans("classUpload_2") . implode(", ", $this->settings['types']) . "."
                );
            }
            
            return $this->settings['chunkSize'];
        }
    }
    
    private function start() {
        if ($this->tmp == "0")
            $this->tmp = uniqid(mt_rand(), true) . ".tmp";
        
        $content = file_get_contents("php://input");

        foreach($this->settings['paths'] as $key => $value) {
            $fopen = fopen("$value/$this->tmp", "a");

            if ($this->checkChunkSize($value) == false) {
                fclose($fopen);
                
                return Array(
                    'status' => 1,
                    'text' => $this->utility->getTranslator()->trans("classUpload_4")
                );
            }
            else {
                fwrite($fopen, $content);
                fclose($fopen);
                
                if ($key == count($this->settings['paths']) - 1) {
                    return Array(
                        'status' => 0,
                        'tmp' => $this->tmp
                    );
                }
            }
        }
    }
    
    private function finish() {
        foreach($this->settings['paths'] as $key => $value) {
            if (file_exists("$value/$this->tmp") == true) {
                if ($this->settings['nameOverwrite'] != "")
                    $this->name =  $this->settings['nameOverwrite'] . "." . pathinfo($this->name, PATHINFO_EXTENSION);

                @rename("$value/$this->tmp", "$value/$this->name");

                if ($key == count($this->settings['paths']) - 1) {
                    return Array(
                        'status' => 2,
                        'text' => $this->utility->getTranslator()->trans("classUpload_5")
                    );
                }
            }
            else {
                return Array(
                    'status' => 2,
                    'text' => $this->utility->getTranslator()->trans("classUpload_6")
                );
            }
        }
    }
    
    private function abort() {
        foreach($this->settings['paths'] as $key => $value) {
            if (file_exists("$value/$this->tmp") == true)
                unlink("$value/$this->tmp");
        }
        
        return Array(
            'status' => 2,
            'text' => $this->utility->getTranslator()->trans("classUpload_7")
        );
    }
    
    private function checkChunkSize($value) {
        $fopen = fopen($value . "/check_" . $this->tmp, "a");
        $fstat = fstat($fopen);
        $size = array_slice($fstat, 13)['size'];
        fclose($fopen);

        if ($size > $this->settings['chunkSize']) {
            unlink($value . "/check_" . $this->tmp);

            return false;
        }
        else
            unlink($value . "/check_" . $this->tmp);
        
        return true;
    }
}