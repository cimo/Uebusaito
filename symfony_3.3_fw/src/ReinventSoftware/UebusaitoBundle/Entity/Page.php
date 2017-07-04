<?php
namespace ReinventSoftware\UebusaitoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="pages", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="ReinventSoftware\UebusaitoBundle\Entity\Repository\PageRepository")
 */
class Page {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="parent", type="integer", nullable=true, columnDefinition="int(11)")
     */
    private $parent;
    
    // #
    private $language;
    
    // #
    private $title;
    
    /**
     * @ORM\Column(name="controller_action", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $controllerAction;
    
    // #
    private $argument;
    
    /**
     * @ORM\Column(name="protected", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '0'")
     */
    private $protected;
    
    /**
     * @ORM\Column(name="role_id", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT '1,2,'")
     */
    private $roleId;
    
    /**
     * @ORM\Column(name="show_in_menu", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '1'")
     */
    private $showInMenu;
    
    // #
    private $menuName;
    
    /**
     * @ORM\Column(name="only_link", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '1'")
     */
    private $onlyLink;
    
    /**
     * @ORM\Column(name="link", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $link;
    
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
    
    public function setProtected($value) {
        $this->protected = $value;
    }
    
    public function setRoleId($value) {
        $this->roleId = $value;
    }
    
    public function setShowInMenu($value) {
        $this->showInMenu = $value;
    }
    
    public function setMenuName($value) {
        $this->menuName = $value;
    }
    
    public function setOnlyLink($value) {
        $this->onlyLink = $value;
    }
    
    public function setLink($value) {
        $this->link = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
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
        return $this->argument;
    }
    
    public function getProtected() {
        return $this->protected;
    }
    
    public function getRoleId() {
        return $this->roleId;
    }
    
    public function getShowInMenu() {
        return $this->showInMenu;
    }
    
    public function getMenuName() {
        return $this->menuName;
    }
    
    public function getOnlyLink() {
        return $this->onlyLink;
    }
    
    public function getLink() {
        return $this->link;
    }
}