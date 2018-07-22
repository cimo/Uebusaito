<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="payments", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="user_id", type="integer", columnDefinition="int(11) NOT NULL")
     */
    private $userId;
    
    /**
     * @ORM\Column(name="transaction", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $transaction;
    
    /**
     * @ORM\Column(name="date", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $date;
    
    /**
     * @ORM\Column(name="status", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $status;
    
    /**
     * @ORM\Column(name="payer", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $payer;
    
    /**
     * @ORM\Column(name="receiver", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $receiver;
    
    /**
     * @ORM\Column(name="currency_code", type="string", columnDefinition="varchar(3) NOT NULL")
     */
    private $currencyCode;
    
    /**
     * @ORM\Column(name="item_name", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $itemName;
    
    /**
     * @ORM\Column(name="amount", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $amount;
    
    /**
     * @ORM\Column(name="quantity", type="string", columnDefinition="varchar(255) NOT NULL")
     */
    private $quantity;
    
    // Properties
    public function setUserId($value) {
        $this->userId = $value;
    }
    
    public function setTransaction($value) {
        $this->transaction = $value;
    }
    
    public function setDate($value) {
        $this->date = $value;
    }
    
    public function setStatus($value) {
        $this->status = $value;
    }
    
    public function setPayer($value) {
        $this->payer = $value;
    }
    
    public function setReceiver($value) {
        $this->receiver = $value;
    }
    
    public function setCurrencyCode($value) {
        $this->currencyCode = $value;
    }
    
    public function setItemName($value) {
        $this->itemName = $value;
    }
    
    public function setAmount($value) {
        $this->amount = $value;
    }
    
    public function setQuantity($value) {
        $this->quantity = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function getTransaction() {
        return $this->transaction;
    }
    
    public function getDate() {
        return $this->date;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function getPayer() {
        return $this->payer;
    }
    
    public function getReceiver() {
        return $this->receiver;
    }
    
    public function getCurrencyCode() {
        return $this->currencyCode;
    }
    
    public function getItemName() {
        return $this->itemName;
    }
    
    public function getAmount() {
        return $this->amount;
    }
    
    public function getQuantity() {
        return $this->quantity;
    }
}