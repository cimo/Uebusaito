<?php
namespace ReinventSoftware\UebusaitoBundle\Classes;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;

class Upload {
    // Vars
    private $container;
    private $entityManager;
    
    private $utility;
    private $utilityPrivate;
    
    private $path;
    private $maxSize;
    private $chunkSize;
    
    private $tmp;
    private $name;
    
    // Properties
    
    // Functions public
    public function __construct($container, $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        
        $this->path = "";
        $this->maxSize = 0;
        $this->chunkSize = 0;
        
        $this->tmp = 0;
        $this->name = "";
    }
    
    public function processFile($path, $maxSize, $chunkSize) {
        $this->path = $path;
        $this->maxSize = $maxSize;
        $this->chunkSize = $chunkSize;
        
        $action = "";
        
        if (isset($_GET['action']) == true)
            $action = $_GET['action'];
        
        if (isset($_GET['tmp']) == true)
            $this->tmp = $_GET['tmp'];
        
        if (isset($_GET['name']) == true)
            $this->name = $_GET['name'];
        
        if ($action == "start")
            return $this->start();
        else if ($action == "finish")
            return $this->finish();
        else if ($action == "abort")
            return $this->abort();
    }
    
    // Functions private
    function start() {
        if ($this->tmp == 0)
            $this->tmp = mt_rand() . ".tmp";

        $content = file_get_contents("php://input");

        if ($content != false) {
            if ($this->checkChunkSize() == false) {
                return Array(
                    'status' => 1,
                    'text' => $this->utility->getTranslator()->trans("class_upload_1")
                );
            }
            
            $fopen = fopen($this->path . "/" . $this->tmp, "a");
            
            if ($this->checkMaxSize($fopen) == false) {
                return Array(
                    'status' => 1,
                    'text' => $this->utility->getTranslator()->trans("class_upload_2")
                );
            }
            else {
                fwrite($fopen, $content);
                fclose($fopen);
                
                return Array(
                    'status' => 0,
                    'tmp' => $this->tmp
                );
            }
        }
        else {
            return Array(
                'status' => 2
            );
        }
    }
    
    function finish() {
        if (file_exists($this->path . "/" . $this->tmp) == true) {
            if (rename($this->path . "/" . $this->tmp, $this->path . "/" . $this->name) == true) {
                return Array(
                    'text' => $this->utility->getTranslator()->trans("class_upload_3")
                );
            }
        }
        else {
            return Array(
                'text' => $this->utility->getTranslator()->trans("class_upload_4") . $this->path
            );
        }
    }
    
    function abort() {
        if (file_exists($this->path . "/" . $this->tmp) == true) {
            if (unlink($this->path . "/" . $this->tmp) == true) {
                return Array(
                    'text' => $this->utility->getTranslator()->trans("class_upload_5")
                );
            }
        }
    }
    
    function checkChunkSize() {
        $fopen = fopen($this->path . "/check_" . $this->tmp, "a");
        $fstat = fstat($fopen);
        $size = array_slice($fstat, 13)['size'];
        fclose($fopen);

        if ($size > $this->chunkSize) {
            unlink($this->path . "/check_" . $this->tmp);

            return false;
        }
        else
            unlink($this->path . "/check_" . $this->tmp);
        
        return true;
    }
    
    function checkMaxSize($fopen) {
        $fstat = fstat($fopen);
        $size = array_slice($fstat, 13)['size'];

        if ($size > $this->maxSize) {
            fclose($fopen);
                
            $this->abort();

            return false;
        }
        
        return true;
    }
}