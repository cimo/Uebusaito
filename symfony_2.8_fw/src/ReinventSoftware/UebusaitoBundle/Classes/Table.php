<?php
namespace ReinventSoftware\UebusaitoBundle\Classes;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;

class Table {
    // Vars
    private $container;
    private $entityManager;
    
    private $utility;
    private $utilityPrivate;
    
    private $searchIndex;
    private $paginationIndex;
    
    // Properties
    
    // Functions public
    public function __construct($container, $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        
        $this->searchIndex = "";
        $this->paginationIndex = "";
    }
    
    public function request($rows, $page, $sessionTag, $reverse, $flat) {
        $newRows = $rows;
        
        if ($reverse == true)
            $newRows = array_reverse($rows, true);
        
        // Table - search
        $searchWritten = isset($_POST['searchWritten']) == true ? $_POST['searchWritten'] : -1;
        $search = $this->search($sessionTag . "Search", $searchWritten);
        $elements = $this->utility->arrayLike($newRows, $search['value'], $flat);
        
        // Table - pagination
        $paginationCurrent = isset($_POST['paginationCurrent']) == true ? $_POST['paginationCurrent'] : -1;
        $pagination = $this->pagination($sessionTag . "Pagination", $paginationCurrent, count($elements), $page);
        
        if ($sessionTag != "page")
            $list = array_slice($elements, $pagination['offset'], $pagination['show']);
        else
            $list = $this->utilityPrivate->createPagesList($elements, false, $pagination);
        
        return Array(
            'search' => $search,
            'pagination' => $pagination,
            'list' => $list
        );
    }
    
    private function search($index, $value) {
        $this->searchIndex = $index;
        
        if (isset($_SESSION[$index]) == false)
            $_SESSION[$index] = "";
        else if ($value != -1)
            $_SESSION[$index] = $value;
        
        return Array(
            'value' => $_SESSION[$index]
        );
    }
    
    private function pagination($index, $value, $count, $show) {
        $this->paginationIndex = $index;
        
        if (isset($_SESSION[$index]) == false)
            $_SESSION[$index] = "";
        if ($value > -1)
            $_SESSION[$index] = $value;
        
        $total = ceil($count / $show);
        $current = $total == 0 ? 0 : $_SESSION[$index] + 1;
        
        if ($_SESSION[$index] > $total)
            $_SESSION[$index] = $total;
        
        $offset = $_SESSION[$index] * $show;
        $text = "$current / $total";
        $limit = "$offset,$show";
        
        return Array(
            'show' => $show,
            'offset' => $offset,
            'text' => $text,
            'limit' => $limit
        );
    }
    
    // Functions private
}