<?php
namespace ReinventSoftware\UebusaitoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="pages_comments", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="ReinventSoftware\UebusaitoBundle\Repository\PageCommentRepository")
 */
class PageComment {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="page_id", type="integer", columnDefinition="int(11) NOT NULL DEFAULT '0'")
     */
    private $pageId = 0;
    
    /**
     * @ORM\Column(name="username", type="string", columnDefinition="varchar(20) NOT NULL DEFAULT ''")
     */
    private $username = "";
    
    /**
     * @ORM\Column(name="username_reply", type="string", nullable=true, columnDefinition="varchar(20)")
     */
    private $usernameReply = null;
    
    /**
     * @ORM\Column(name="argument", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $argument = "";
    
    /**
     * @ORM\Column(name="date_creation", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateCreation = "";
    
    /**
     * @ORM\Column(name="date_modification", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateModification = "";
    
    // Properties
    public function setPageId($value) {
        $this->pageId = $value;
    }
    
    public function setUsername($value) {
        $this->username = $value;
    }
    
    public function setUsernameReply($value) {
        $this->usernameReply = $value;
    }
    
    public function setArgument($value) {
        $this->argument = $value;
    }
    
    public function setDateCreation($value) {
        $this->dateCreation = $value;
    }
    
    public function setDateModification($value) {
        $this->dateModification = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getPageId() {
        return $this->pageId;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getUsernameReply() {
        return $this->usernameReply;
    }
    
    public function getArgument() {
        return $this->argument;
    }
    
    public function getDateCreation() {
        return $this->dateCreation;
    }
    
    public function getDateModification() {
        return $this->dateModification;
    }
}