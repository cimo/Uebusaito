<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="microservice_api", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\MicroserviceApiRepository")
 * @UniqueEntity(fields={"name"}, groups={"settingSlackIw"})
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
     * @ORM\Column(name="controller", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $controller = "";
    
    /**
     * @ORM\Column(name="description", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $description = "";
    
    /**
     * @ORM\Column(name="image", type="string", nullable=true, columnDefinition="varchar(255) DEFAULT NULL")
     */
    private $image = "";
    
    private $removeImage = false;
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $active = false;
    
    // Properties
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setController($value) {
        $this->controller = $value;
    }
    
    public function setDescription($value) {
        $this->description = $value;
    }
    
    public function setImage($value) {
        $this->image = $value;
    }
    
    public function setRemoveImage($value) {
        $this->removeImage = $value;
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
    
    public function getController() {
        return $this->controller;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getImage() {
        return $this->image;
    }
    
    public function getRemoveImage() {
        return $this->removeImage;
    }
    
    public function getActive() {
        return $this->active;
    }
}