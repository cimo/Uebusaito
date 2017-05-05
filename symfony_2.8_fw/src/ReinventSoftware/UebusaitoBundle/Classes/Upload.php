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
    private $key;
    private $name;
    
    // Properties
    
    // Functions public
    public function __construct($container, $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        
        $this->path = "";
        $this->key = 0;
        $this->name = "";
    }
    
    public function processFile($path) {
        $this->path = $path;
        
        $action = "";
        
        if (isset($_GET['action']) == true)
            $action = $_GET['action'];
	else if (isset($_POST['action']) == true)
            $action = $_POST['action'];
        
        if (isset($_GET['key']) == true)
            $this->key = $_GET['key'];
	else if (isset($_POST['key']) == true)
            $this->key = $_POST['key'];
        
        if (isset($_GET['name']) == true)
            $this->name = $_GET['name'];
	else if (isset($_POST['name']) == true)
            $this->name = $_POST['name'];
        
        if ($action == "upload")
            return $this->upload();
        else if ($action == "finish")
            return $this->finish();
        else if ($action == "abort")
            return $this->abort();
    }
    
    // Functions private
    function upload() {
        if ($this->key == 0)
            $this->key = mt_rand() . ".tmp";

        $content = file_get_contents("php://input");

        if ($content != false) {
            $fopen = fopen($this->path . "/" . $this->key, "a");
            fwrite($fopen, $content);
            fclose($fopen);

            return Array(
                'status' => 0,
                'key' => $this->key
            );
        }
        else {
            return Array(
                'status' => 1
            );
        }
    }
    
    function finish() {
        if (file_exists($this->path . "/" . $this->key) == true) {
            if (rename($this->path . "/" . $this->key, $this->path . "/" . $this->name) == true) {
                return Array(
                    'text' => "File uploaded successfully."
                );
            }
        }
        else {
            return Array(
                'text' => "Unable to move file to " . $this->path
            );
        }
    }
    
    function abort() {
        if (file_exists($this->path . "/" . $this->key) == true) {
            if (unlink($this->path . "/" . $this->key) == true) {
                return Array(
                    'status' => 0
                );
            }
        }
    }
}