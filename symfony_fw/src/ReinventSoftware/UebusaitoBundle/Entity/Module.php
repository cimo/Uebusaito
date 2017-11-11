<?php
namespace ReinventSoftware\UebusaitoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="modules", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="ReinventSoftware\UebusaitoBundle\Entity\Repository\ModuleRepository")
 * @UniqueEntity(fields={"name"}, groups={"module_creation", "module_profile"})
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
    private $position = "";
    
    /**
     * @ORM\Column(name="position_tmp", type="string", nullable=true, columnDefinition="varchar(6)")
     */
    private $positionTmp = null;
    
    /**
     * @ORM\Column(name="position_in_column", type="integer", nullable=true, columnDefinition="int(11)")
     */
    private $positionInColumn = null;
    
    // #
    private $sort = null;
    
    /**
     * @ORM\Column(name="name", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $name = "";
    
    /**
     * @ORM\Column(name="label", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $label = null;
    
    /**
     * @ORM\Column(name="file_name", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $fileName = "";
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '0'")
     */
    private $active = false;

    // Properties
    public function setPosition($value) {
        $this->position = $value;
    }
    
    public function setPositionTmp($value) {
        $this->positionTmp = $value;
    }
    
    public function setPositionInColumn($value) {
        $this->positionInColumn = $value;
    }
    
    public function setSort($value) {
        $this->sort = $value;
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
    
    public function getPosition() {
        return $this->position;
    }
    
    public function getPositionTmp() {
        return $this->positionTmp;
    }
    
    public function getPositionInColumn() {
        return $this->positionInColumn;
    }
    
    public function getSort() {
        return $this->sort;
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