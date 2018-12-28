<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="settings_line_push", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\SettingLinePushRepository")
 * @UniqueEntity(fields={"name"}, groups={"setting_line_push"})
 */
class SettingLinePush {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    private $event = "";
    
    /**
     * @ORM\Column(name="name", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $name = "";
    
    /**
     * @ORM\Column(name="user_id", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $userId = "";
    
    /**
     * @ORM\Column(name="access_token", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $accessToken = "";
    
    // Properties
    public function setEvent($value) {
        $this->event = $value;
    }
    
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setUserId($value) {
        $this->userId = $value;
    }
    
    public function setAccessToken($value) {
        $this->accessToken = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getEvent() {
        return $this->event;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function getAccessToken() {
        return $this->accessToken;
    }
}