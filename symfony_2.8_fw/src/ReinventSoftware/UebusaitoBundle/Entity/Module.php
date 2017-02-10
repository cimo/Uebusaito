<?php
namespace ReinventSoftware\UebusaitoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="modules", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="ReinventSoftware\UebusaitoBundle\Entity\Repository\ModuleRepository")
 */
class Module {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="position", type="string", columnDefinition="varchar(6) NOT NULL DEFAULT 'center'")
     */
    private $position;
    
    /**
     * @ORM\Column(name="sort", type="integer", columnDefinition="int(11) NULL DEFAULT '0'")
     */
    private $sort;
    
    /**
     * @ORM\Column(name="name", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $name;
    
    /**
     * @ORM\Column(name="label", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $label;
    
    /**
     * @ORM\Column(name="file_name", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $fileName;
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NULL DEFAULT '0'")
     */
    private $active;

    public function setSort($value) {
        $this->sort = $value;
    }
    
    public function setPosition($value) {
        $this->position = $value;
    }
    
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setLabel($value) {
        $this->label = $value;
    }
    
    public function setFileName($value) {
        $this->fileName = $value;
    }
    
    public function setActive($value) {
        $this->active = $value;
    }
    // ---
    public function getId() {
        return $this->id;
    }
    
    public function getSort() {
        return $this->sort;
    }
    
    public function getPosition() {
        return $this->position;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getLabel() {
        return $this->label;
    }
    
    public function getFileName() {
        return $this->fileName;
    }
    
    public function getActive() {
        return $this->active;
    }
}