<?php
namespace ReinventSoftware\UebusaitoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="languages", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="ReinventSoftware\UebusaitoBundle\Entity\Repository\LanguageRepository")
 */
class Language {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="code", type="string", columnDefinition="varchar(2) NOT NULL")
     */
    private $code;

    public function setCode($value) {
        $this->code = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getCode() {
        return $this->code;
    }
}