<?php
namespace ReinventSoftware\UebusaitoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="languages", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="ReinventSoftware\UebusaitoBundle\Repository\LanguageRepository")
 */
class Language {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="code", type="string", columnDefinition="varchar(2) NOT NULL DEFAULT ''")
     */
    private $code = "";
    
    /**
     * @ORM\Column(name="date", type="string", columnDefinition="varchar(5) NOT NULL DEFAULT 'Y-m-d'")
     */
    private $date = "";
    
    // Properties
    public function setCode($value) {
        $this->code = $value;
    }
    
    public function setDate($value) {
        $this->date = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getCode() {
        return $this->code;
    }
    
    public function getDate() {
        return $this->date;
    }
}