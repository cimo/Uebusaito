<?php
namespace ReinventSoftware\UebusaitoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Table(name="users", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="ReinventSoftware\UebusaitoBundle\Entity\Repository\UserRepository")
 * @UniqueEntity(fields={"username"}, groups={"registration", "user_profile"})
 * @UniqueEntity(fields={"email"}, groups={"registration", "profile", "user_profile"})
 */
class User implements AdvancedUserInterface {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="role_id", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT '1,2,'")
     */
    private $roleId;
    
    /**
     * @ORM\Column(name="username", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $username;
    
    /**
     * @ORM\Column(name="name", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $name;
    
    /**
     * @ORM\Column(name="surname", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $surname;
    
    /**
     * @ORM\Column(name="email", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $email;
    
    /**
     * @ORM\Column(name="telephone", type="string", nullable=true, columnDefinition="varchar(10)")
     */
    private $telephone;
    
    /**
     * @ORM\Column(name="born", type="string", nullable=true, columnDefinition="varchar(10) DEFAULT '0000-00-00'")
     */
    private $born;
    
    /**
     * @ORM\Column(name="gender", type="string", nullable=true, columnDefinition="varchar(6) DEFAULT 'male'")
     */
    private $gender;
    
    /**
     * @ORM\Column(name="fiscal_code", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $fiscalCode;
    
    /**
     * @ORM\Column(name="company_name", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $companyName;
    
    /**
     * @ORM\Column(name="company_code", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $companyCode;
    
    /**
     * @ORM\Column(name="website", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $website;
    
    /**
     * @ORM\Column(name="state", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $state;
    
    /**
     * @ORM\Column(name="city", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $city;
    
    /**
     * @ORM\Column(name="zip", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $zip;
    
    /**
     * @ORM\Column(name="address", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $address;
    
    /**
     * @ORM\Column(name="password", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $password;
    
    /**
     * @ORM\Column(name="credits", type="integer", columnDefinition="int(11) NOT NULL DEFAULT '0'")
     */
    private $credits;
    
    /**
     * @ORM\Column(name="not_locked", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '0'")
     */
    private $notLocked;
    
    /**
     * @ORM\Column(name="date_registration", type="string", nullable=true, columnDefinition="varchar(19) DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateRegistration;
    
    /**
     * @ORM\Column(name="date_last_login", type="string", nullable=true, columnDefinition="varchar(19) DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateLastLogin;
    
    /**
     * @ORM\Column(name="help_code", type="string", columnDefinition="varchar(255)")
     */
    private $helpCode;
    
    public function setRoleId($value) {
        $this->roleId = $value;
    }
    
    public function setUsername($value) {
        $this->username = $value;
    }

    public function setName($value) {
        $this->name = $value;
    }

    public function setSurname($value) {
        $this->surname = $value;
    }

    public function setEmail($value) {
        $this->email = $value;
    }

    public function setTelephone($value) {
        $this->telephone = $value;
    }

    public function setBorn($value) {
        $this->born = $value;
    }

    public function setGender($value) {
        $this->gender = $value;
    }

    public function setFiscalCode($value) {
        $this->fiscalCode = $value;
    }

    public function setCompanyName($value) {
        $this->companyName = $value;
    }

    public function setCompanyCode($value) {
        $this->companyCode = $value;
    }

    public function setWebsite($value) {
        $this->website = $value;
    }

    public function setAddress($value) {
        $this->address = $value;
    }

    public function setCity($value) {
        $this->city = $value;
    }

    public function setState($value) {
        $this->state = $value;
    }

    public function setZip($value) {
        $this->zip = $value;
    }

    public function setPassword($value) {
        $this->password = $value;
    }
    
    public function setCredits($value) {
        $this->credits = $value;
    }
    
    public function setNotLocked($value) {
        $this->notLocked = $value;
    }
    
    public function setHelpCode($value) {
        $this->helpCode = $value;
    }
    
    public function setDateRegistration($value) {
        $this->dateRegistration = $value;
    }
    
    public function setDateLastLogin($value) {
        $this->dateLastLogin = $value;
    }
    // ---
    public function getId() {
        return $this->id;
    }
    
    public function getRoleId() {
        return $this->roleId;
    }

    public function getName() {
        return $this->name;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getTelephone() {
        return $this->telephone;
    }

    public function getBorn() {
        return $this->born;
    }

    public function getGender() {
        return $this->gender;
    }

    public function getFiscalCode() {
        return $this->fiscalCode;
    }

    public function getCompanyName() {
        return $this->companyName;
    }

    public function getCompanyCode() {
        return $this->companyCode;
    }

    public function getWebsite() {
        return $this->website;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getCity() {
        return $this->city;
    }

    public function getState() {
        return $this->state;
    }

    public function getZip() {
        return $this->zip;
    }
    
    public function getCredits() {
        return $this->credits;
    }
    
    public function getNotLocked() {
        return $this->notLocked;
    }
    
    public function getHelpCode() {
        return $this->helpCode;
    }
    
    public function getDateRegistration() {
        return $this->dateRegistration;
    }
    
    public function getDateLastLogin() {
        return $this->dateLastLogin;
    }
    
    // UserInterface
    public function getUsername() {
        return $this->username;
    }
    
    public function getPassword() {
        return $this->password;
    }
    
    public function eraseCredentials() {
    }

    public function getRoles() {
        return $this->roles;
    }

    public function getSalt() {
        return null;
    }
    
    // AdvanceUserInterface
    public function isAccountNonExpired() {
        return true;
    }

    public function isAccountNonLocked() {
        return $this->notLocked;
    }

    public function isCredentialsNonExpired() {
        return true;
    }

    public function isEnabled() {
        return true;
    }
    
    // Plus
    private $roles = Array();
    
    private $passwordConfirm;
    
    public function setRoles($roles) {
        array_push($roles, "ROLE_USER");
        
        $this->roles = array_unique($roles);
    }
    
    public function setPasswordConfirm($value) {
        $this->passwordConfirm = $value;
    }
    // ---
    public function getPasswordConfirm() {
        return $this->passwordConfirm;
    }
}