<?php
namespace ReinventSoftware\UebusaitoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="settings", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="ReinventSoftware\UebusaitoBundle\Entity\Repository\SettingRepository")
 */
class Setting {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="template", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT 'uebusaito'")
     */
    private $template;
    
    /**
     * @ORM\Column(name="template_column", type="integer", columnDefinition="int(1) NOT NULL DEFAULT '1'")
     */
    private $templateColumn;
    
    /**
     * @ORM\Column(name="language", type="string", columnDefinition="varchar(2) NOT NULL DEFAULT 'en'")
     */
    private $language;
    
    /**
     * @ORM\Column(name="email_admin", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $emailAdmin;
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '1'")
     */
    private $active;
    
    /**
     * @ORM\Column(name="role_id", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT '2,3,'")
     */
    private $roleId;
    
    /**
     * @ORM\Column(name="https", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '1'")
     */
    private $https;
    
    /**
     * @ORM\Column(name="registration_user_confirm_admin", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '1'")
     */
    private $registrationUserConfirmAdmin;
    
    /**
     * @ORM\Column(name="login_attempt_time", type="integer", columnDefinition="int(11) NOT NULL DEFAULT '15'")
     */
    private $loginAttemptTime;
    
    /**
     * @ORM\Column(name="login_attempt_count", type="integer", columnDefinition="int(11) NOT NULL DEFAULT '3'")
     */
    private $loginAttemptCount;
    
    /**
     * @ORM\Column(name="captcha", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '0'")
     */
    private $captcha;
    
    /**
     * @ORM\Column(name="payPal_sandbox", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '0'")
     */
    private $payPalSandbox;
    
    /**
     * @ORM\Column(name="payPal_business", type="string", columnDefinition="varchar(255)")
     */
    private $payPalBusiness;
    
    /**
     * @ORM\Column(name="payPal_currency_code", type="string", columnDefinition="varchar(3) NOT NULL DEFAULT 'USD'")
     */
    private $payPalCurrencyCode;
    
    /**
     * @ORM\Column(name="payPal_credit_amount", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT '0.01'")
     */
    private $payPalCreditAmount;
    
    /**
     * @ORM\Column(name="credits", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT '1'")
     */
    private $credits;
    
    public function setTemplate($value) {
        $this->template = $value;
    }
    
    public function setTemplateStyle($value) {
        $this->templateColumn = $value;
    }
    
    public function setLanguage($value) {
        $this->language = $value;
    }
    
    public function setEmailAdmin($value) {
        $this->emailAdmin = $value;
    }
    
    public function setActive($value) {
        $this->active = $value;
    }
    
    public function setRoleId($value) {
        $this->roleId = $value;
    }
    
    public function setHttps($value) {
        $this->https = $value;
    }
    
    public function setRegistrationUserConfirmAdmin($value) {
        $this->registrationUserConfirmAdmin = $value;
    }
    
    public function setLoginAttemptTime($value) {
        $this->loginAttemptTime = $value;
    }
    
    public function setLoginAttemptCount($value) {
        $this->loginAttemptCount = $value;
    }
    
    public function setCaptcha($value) {
        $this->captcha = $value;
    }
    
    public function setPayPalSandbox($value) {
        $this->payPalSandbox = $value;
    }
    
    public function setPayPalBusiness($value) {
        $this->payPalBusiness = $value;
    }
    
    public function setPayPalCurrencyCode($value) {
        $this->payPalCurrencyCode = $value;
    }
    
    public function setPayPalCreditAmount($value) {
        $this->payPalCreditAmount = $value;
    }
    
    public function setCredits($value) {
        $this->credits = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getTemplate() {
        return $this->template;
    }
    
    public function getTemplateColumn() {
        return $this->templateColumn;
    }
    
    public function getLanguage() {
        return $this->language;
    }
    
    public function getEmailAdmin() {
        return $this->emailAdmin;
    }
    
    public function getActive() {
        return $this->active;
    }
    
    public function getRoleId() {
        return $this->roleId;
    }
    
    public function getHttps() {
        return $this->https;
    }
    
    public function getRegistrationUserConfirmAdmin() {
        return $this->registrationUserConfirmAdmin;
    }
    
    public function getLoginAttemptTime() {
        return $this->loginAttemptTime;
    }
    
    public function getLoginAttemptCount() {
        return $this->loginAttemptCount;
    }
    
    public function getCaptcha() {
        return $this->captcha;
    }
    
    public function getPayPalSandbox() {
        return $this->payPalSandbox;
    }
    
    public function getPayPalBusiness() {
        return $this->payPalBusiness;
    }
    
    public function getPayPalCurrencyCode() {
        return $this->payPalCurrencyCode;
    }
    
    public function getPayPalCreditAmount() {
        return $this->payPalCreditAmount;
    }
    
    public function getCredits() {
        return $this->credits;
    }
}