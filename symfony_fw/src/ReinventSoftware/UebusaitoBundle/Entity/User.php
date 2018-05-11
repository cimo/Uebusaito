<?php
namespace ReinventSoftware\UebusaitoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * @ORM\Table(name="users", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="ReinventSoftware\UebusaitoBundle\Entity\Repository\UserRepository")
 * @UniqueEntity(fields={"username"}, groups={"registration", "user_creation", "user_profile"})
 * @UniqueEntity(fields={"email"}, groups={"registration", "user_creation", "user_profile"})
 */
class User implements UserInterface, AdvancedUserInterface, EquatableInterface, \Serializable {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="role_user_id", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT '1,'")
     */
    private $roleUserId = "1,";
    
    /**
     * @ORM\Column(name="username", type="string", columnDefinition="varchar(20) NOT NULL DEFAULT ''")
     */
    private $username = "";
    
    /**
     * @ORM\Column(name="name", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $name = null;
    
    /**
     * @ORM\Column(name="surname", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $surname = null;
    
    /**
     * @ORM\Column(name="email", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $email = "";
    
    /**
     * @ORM\Column(name="telephone", type="string", nullable=true, columnDefinition="varchar(10)")
     */
    private $telephone = null;
    
    /**
     * @ORM\Column(name="born", type="string", columnDefinition="varchar(10) NOT NULL DEFAULT '0000-00-00'")
     */
    private $born = "";
    
    /**
     * @ORM\Column(name="gender", type="string", nullable=true, columnDefinition="varchar(6)")
     */
    private $gender = null;
    
    /**
     * @ORM\Column(name="fiscal_code", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $fiscalCode = null;
    
    /**
     * @ORM\Column(name="company_name", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $companyName = null;
    
    /**
     * @ORM\Column(name="vat", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $vat = null;
    
    /**
     * @ORM\Column(name="website", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $website = null;
    
    /**
     * @ORM\Column(name="state", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $state = null;
    
    /**
     * @ORM\Column(name="city", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $city = null;
    
    /**
     * @ORM\Column(name="zip", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $zip = null;
    
    /**
     * @ORM\Column(name="address", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $address = null;
    
    /**
     * @ORM\Column(name="password", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $password = "";
    
    /**
     * @ORM\Column(name="credit", type="integer", columnDefinition="int(11) NOT NULL DEFAULT '0'")
     */
    private $credit = 0;
    
    /**
     * @ORM\Column(name="not_locked", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '0'")
     */
    private $notLocked = false;
    
    /**
     * @ORM\Column(name="date_registration", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateRegistration = "";
    
    /**
     * @ORM\Column(name="date_current_login", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateCurrentLogin = "";
    
    /**
     * @ORM\Column(name="date_last_login", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateLastLogin = "";
    
    /**
     * @ORM\Column(name="help_code", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $helpCode = null;
    
    /**
     * @ORM\Column(name="ip", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $ip = null;
    
    /**
     * @ORM\Column(name="attempt_login", type="integer", columnDefinition="int(11) NOT NULL DEFAULT '0'")
     */
    private $attemptLogin = 0;
    
    // Properties
    public function setRoleUserId($value) {
        $this->roleUserId = $value;
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

    public function setVat($value) {
        $this->vat = $value;
    }

    public function setWebsite($value) {
        $this->website = $value;
    }
    
    public function setState($value) {
        $this->state = $value;
    }
    
    public function setCity($value) {
        $this->city = $value;
    }

    public function setZip($value) {
        $this->zip = $value;
    }
    
    public function setAddress($value) {
        $this->address = $value;
    }

    public function setPassword($value) {
        $this->password = $value;
    }
    
    public function setCredit($value) {
        $this->credit = $value;
    }
    
    public function setNotLocked($value) {
        $this->notLocked = $value;
    }
    
    public function setDateRegistration($value) {
        $this->dateRegistration = $value;
    }
    
    public function setDateCurrentLogin($value) {
        $this->dateCurrentLogin = $value;
    }
    
    public function setDateLastLogin($value) {
        $this->dateLastLogin = $value;
    }
    
    public function setHelpCode($value) {
        $this->helpCode = $value;
    }
    
    public function setIp($value) {
        $this->ip = $value;
    }
    
    public function setAttemptLogin($value) {
        $this->attemptLogin = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getRoleUserId() {
        return $this->roleUserId;
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

    public function getVat() {
        return $this->vat;
    }

    public function getWebsite() {
        return $this->website;
    }
    
    public function getState() {
        return $this->state;
    }

    public function getCity() {
        return $this->city;
    }
    
    public function getZip() {
        return $this->zip;
    }
    
    public function getAddress() {
        return $this->address;
    }
    
    public function getCredit() {
        return $this->credit;
    }
    
    public function getNotLocked() {
        return $this->notLocked;
    }
    
    public function getDateRegistration() {
        return $this->dateRegistration;
    }
    
    public function getDateCurrentLogin() {
        return $this->dateCurrentLogin;
    }
    
    public function getDateLastLogin() {
        return $this->dateLastLogin;
    }
    
    public function getHelpCode() {
        return $this->helpCode;
    }
    
    public function getIp() {
        return $this->ip;
    }
    
    public function getAttemptLogin() {
        return $this->attemptLogin;
    }
    
    // Plus
    private $roles = Array();
    
    private $passwordConfirm = "";
    
    public function setRoles($roles) {
        array_push($roles, "ROLE_USER");
        
        $this->roles = array_unique($roles);
    }
    
    public function setPasswordConfirm($value) {
        $this->passwordConfirm = $value;
    }
    
    // ---
    
    public function getRoles() {
        return $this->roles;
    }
    
    public function getPasswordConfirm() {
        return $this->passwordConfirm;
    }
    
    // UserInterface
    private $salt = null;
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getPassword() {
        return $this->password;
    }
    
    public function getSalt() {
        return $this->salt;
    }
    
    public function eraseCredentials() {
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
    
    public function hasUserChanged() {
        return false;
    }
    
    public function doesUserNeedReloading() {
        return false;
    }
    
    // EquatableInterface
    function isEqualTo(UserInterface $user) {
        /*if (!$user instanceof User)
            return false;
        
        if ($this->username !== $user->getUsername())
            return false;
        
        if ($this->password !== $user->getPassword())
            return false;
        
        if ($this->salt !== $user->getSalt())
            return false;
        
        if ($this->roles !== $user->getRoles())
            return false;
        
        if ($this->dateCurrentLogin !== $user->getDateCurrentLogin())
            return true;
        
        if ($this->dateLastLogin !== $user->getDateLastLogin())
            return true;
        
        return true;*/
        
        /*if ($user instanceof User) {
            $isEqual = count($this->getRoles()) == count($user->getRoles());
            
            if ($isEqual) {
                foreach($this->getRoles() as $role) {
                    $isEqual = $isEqual && in_array($role, $user->getRoles());
                }
            }
            
            if ($this->dateCurrentLogin == $user->getDateCurrentLogin())
                $isEqual = false;

            if ($this->dateLastLogin == $user->getDateLastLogin())
                $isEqual = false;

            return $isEqual;
        }*/

        return true;
    }
    
    /** @see \Serializable::serialize() */
    public function serialize() {
        return serialize(Array(
            $this->id,
            $this->roleUserId,
            $this->username,
            $this->name,
            $this->surname,
            $this->email,
            $this->telephone,
            $this->born,
            $this->gender,
            $this->fiscalCode,
            $this->companyName,
            $this->vat,
            $this->website,
            $this->state,
            $this->city,
            $this->zip,
            $this->address,
            $this->password,
            $this->credit,
            $this->notLocked,
            //$this->dateRegistration,
            //$this->dateCurrentLogin,
            //$this->dateLastLogin,
            $this->helpCode,
            $this->ip,
            $this->attemptLogin
        ));
    }
    
    /** @see \Serializable::unserialize() */
    public function unserialize($serialized) {
        list (
            $this->id,
            $this->roleUserId,
            $this->username,
            $this->name,
            $this->surname,
            $this->email,
            $this->telephone,
            $this->born,
            $this->gender,
            $this->fiscalCode,
            $this->companyName,
            $this->vat,
            $this->website,
            $this->state,
            $this->city,
            $this->zip,
            $this->address,
            $this->password,
            $this->credit,
            $this->notLocked,
            //$this->dateRegistration,
            //$this->dateCurrentLogin,
            //$this->dateLastLogin,
            $this->helpCode,
            $this->ip,
            $this->attemptLogin
        ) = unserialize($serialized);
    }
}