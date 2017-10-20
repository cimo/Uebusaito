<?php
namespace ReinventSoftware\UebusaitoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="users_roles", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="ReinventSoftware\UebusaitoBundle\Entity\Repository\RoleRepository")
 */
class Role {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="level", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT 'ROLE_USER'")
     */
    private $level;
    
    // Properties
    public function setLevel($value) {
        $this->level = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getLevel() {
        return $this->level;
    }
}