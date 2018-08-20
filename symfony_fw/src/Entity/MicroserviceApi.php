<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="microservice_api", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\MicroserviceApiRepository")
 */
class MicroserviceApi {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="name", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $name = "";
    
    /**
     * @ORM\Column(name="folder_name", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $folderName = "";
    
    /**
     * @ORM\Column(name="description", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $description = "";
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '0'")
     */
    private $active = false;
    
    // Properties
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setFolderName($value) {
        $this->folderName = $value;
    }
    
    public function setDescription($value) {
        $this->description = $value;
    }
    
    public function setActive($value) {
        $this->active = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getFolderName() {
        return $this->folderName;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getActive() {
        return $this->active;
    }
}