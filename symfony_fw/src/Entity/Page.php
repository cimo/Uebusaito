<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="pages", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\PageRepository")
 * @UniqueEntity(fields={"alias"}, groups={"page_creation", "page_profile"})
 */
class Page {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="alias", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $alias = "";
    
    /**
     * @ORM\Column(name="parent", type="integer", nullable=true, columnDefinition="int(11)")
     */
    private $parent = null;
    
    // #
    private $language = null;
    
    // #
    private $title = null;
    
    /**
     * @ORM\Column(name="controller_action", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $controllerAction = null;
    
    // #
    private $argument = null;
    
    /**
     * @ORM\Column(name="role_user_id", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT '1,2,'")
     */
    private $roleUserId = "";
    
    /**
     * @ORM\Column(name="protected", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '0'")
     */
    private $protected = false;
    
    /**
     * @ORM\Column(name="show_in_menu", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '1'")
     */
    private $showInMenu = true;
    
    /**
     * @ORM\Column(name="rank_in_menu", type="integer", nullable=true, columnDefinition="int(11)")
     */
    private $rankInMenu = null;
    
    // #
    private $rankMenuSort = null;
    
    // #
    private $menuName = null;
    
    /**
     * @ORM\Column(name="comment", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '1'")
     */
    private $comment = true;
    
    /**
     * @ORM\Column(name="only_link", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '0'")
     */
    private $onlyLink = false;
    
    /**
     * @ORM\Column(name="link", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT '-'")
     */
    private $link = "";
    
    /**
     * @ORM\Column(name="user_creation", type="string", columnDefinition="varchar(20) NOT NULL DEFAULT '-'")
     */
    private $userCreation = "";
    
    /**
     * @ORM\Column(name="date_creation", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateCreation = "";
    
    /**
     * @ORM\Column(name="user_modification", type="string", columnDefinition="varchar(20) NOT NULL DEFAULT '-'")
     */
    private $userModification = "";
    
    /**
     * @ORM\Column(name="date_modification", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateModification = "";
    
    // Properties
    public function setAlias($value) {
        $this->alias = $value;
    }
    
    public function setParent($value) {
        $this->parent = $value;
    }
    
    public function setLanguage($value) {
        $this->language = $value;
    }
    
    public function setTitle($value) {
        $this->title = $value;
    }
    
    public function setControllerAction($value) {
        $this->controllerAction = $value;
    }
    
    public function setArgument($value) {
        $this->argument = $value;
    }
    
    public function setRoleUserId($value) {
        $this->roleUserId = $value;
    }
    
    public function setProtected($value) {
        $this->protected = $value;
    }
    
    public function setShowInMenu($value) {
        $this->showInMenu = $value;
    }
    
    public function setRankInMenu($value) {
        $this->rankInMenu = $value;
    }
    
    public function setRankMenuSort($value) {
        $this->rankMenuSort = $value;
    }
    
    public function setMenuName($value) {
        $this->menuName = $value;
    }
    
    public function setComment($value) {
        $this->comment = $value;
    }
    
    public function setOnlyLink($value) {
        $this->onlyLink = $value;
    }
    
    public function setLink($value) {
        $this->link = $value;
    }
    
    public function setUserCreation($value) {
        $this->userCreation = $value;
    }
    
    public function setDateCreation($value) {
        $this->dateCreation = $value;
    }
    
    public function setUserModification($value) {
        $this->userModification = $value;
    }
    
    public function setDateModification($value) {
        $this->dateModification = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getAlias() {
        return $this->alias;
    }
    
    public function getParent() {
        return $this->parent;
    }
    
    public function getLanguage() {
        return $this->language;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getControllerAction() {
        return $this->controllerAction;
    }
    
    public function getArgument() {
        return html_entity_decode($this->argument, ENT_QUOTES, "utf-8");
    }
    
    public function getRoleUserId() {
        return $this->roleUserId;
    }
    
    public function getProtected() {
        return $this->protected;
    }
    
    public function getShowInMenu() {
        return $this->showInMenu;
    }
    
    public function getRankInMenu() {
        return $this->rankInMenu;
    }
    
    public function getRankMenuSort() {
        return $this->rankMenuSort;
    }
    
    public function getMenuName() {
        return $this->menuName;
    }
    
    public function getComment() {
        return $this->comment;
    }
    
    public function getOnlyLink() {
        return $this->onlyLink;
    }
    
    public function getLink() {
        return $this->link;
    }
    
    public function getUserCreation() {
        return $this->userCreation;
    }
    
    public function getDateCreation() {
        return $this->dateCreation;
    }
    
    public function getUserModification() {
        return $this->userModification;
    }
    
    public function getDateModification() {
        return $this->dateModification;
    }
}