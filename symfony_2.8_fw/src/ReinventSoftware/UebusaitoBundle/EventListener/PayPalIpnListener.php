<?php
namespace ReinventSoftware\UebusaitoBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\PayPal;

use ReinventSoftware\UebusaitoBundle\Entity\Payment;

class PayPalIpnListener {
    // Vars
    private $container;
    private $entityManager;
    
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
    }
    
    public function onKernelResponse(FilterResponseEvent $event) {
        $request = $event->getRequest();
        
        if (strpos($request->getUri(), "cp_profile_credits_payPal") !== false) {
            $settingRows = $this->query->selectAllSettingsFromDatabase();
            
            $payPal = new PayPal(true, false, $settingRows['payPal_sandbox']);
            
            if ($payPal->ipn() == true) {
                $payPalElements = $payPal->getElements();

                $paymentRow = $this->query->selectPaymentWithTransactionFromDatabase($payPalElements['txn_id']);

                if ($paymentRow == false && isset($payPalElements['payment_status']) == true) {
                    if ($payPalElements['payment_status'] == "Completed") {
                        $payment = new Payment();
                        $payment->setUserId($payPalElements['custom']);
                        $payment->setTransaction($payPalElements['txn_id']);
                        $payment->setDate($payPalElements['payment_date']);
                        $payment->setStatus($payPalElements['payment_status']);
                        $payment->setPayer($payPalElements['payer_id']);
                        $payment->setReceiver($payPalElements['receiver_id']);
                        $payment->setCurrencyCode($payPalElements['mc_currency']);
                        $payment->setItemName($payPalElements['item_name']);
                        $payment->setAmount($payPalElements['mc_gross']);
                        $payment->setQuantity($payPalElements['quantity']);

                        // Insert in database
                        $this->entityManager->persist($payment);
                        $this->entityManager->flush();

                        $this->updateCredits($payPalElements);

                        error_log("Completed: " . print_r($payPalElements, true) . PHP_EOL);
                    }
                    else if ($payPalElements['payment_status'] == "Pending")
                        error_log("Pending: " . print_r($payPalElements, true) . PHP_EOL);
                }
            }
        }
    }
    
    // Functions private
    private function updateCredits($payPalElements) {
        $userRow = $this->query->selectUserFromDatabase($payPalElements['custom']);
        
        $id = $userRow['id'];
        $credits = $userRow['credits'] + $payPalElements['quantity'];
        
        $query = $this->utility->getConnection()->prepare("UPDATE users
                                                            SET credits = :credits
                                                            WHERE id = :id");
        
        $query->bindValue(":credits", $credits);
        $query->bindValue(":id", $id);
        
        $query->execute();
    }
}