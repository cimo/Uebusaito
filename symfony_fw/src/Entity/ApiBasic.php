<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="microservice_apiBasic", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\ApiBasicRepository")
 * @UniqueEntity(fields={"name"}, groups={"apiBasic_profile"})
 */
class ApiBasic {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="name", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $name = "";
    
    /**
     * @ORM\Column(name="token", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $token = "";
    
    /**
     * @ORM\Column(name="ip", type="string", columnDefinition="varchar(255) DEFAULT ''")
     */
    private $ip = "";
    
    /**
     * @ORM\Column(name="url_callback", type="string", columnDefinition="varchar(255) DEFAULT ''")
     */
    private $urlCallback = "";
    
    /**
     * @ORM\Column(name="active", type="string", columnDefinition="tinyint(11) NOT NULL")
     */
    private $active = "";
    
    // Properties
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setToken($value) {
        $this->token = $value;
    }
    
    public function setIp($value) {
        $this->ip = $value;
    }
    
    public function setUrlCallback($value) {
        $this->urlCallback = $value;
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
    
    public function getToken() {
        return $this->token;
    }
    
    public function getIp() {
        return $this->ip;
    }
    
    public function getUrlCallback() {
        return $this->urlCallback;
    }
    
    public function getActive() {
        return $this->active;
    }
}