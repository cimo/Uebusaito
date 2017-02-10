<?php
namespace ReinventSoftware\UebusaitoBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

use ReinventSoftware\UebusaitoBundle\Entity\Payment;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\PayPal;

class PayPalIpnListener {
    // Vars
    private $container;
    private $entityManager;
    
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->utility = new Utility($this->container, $this->entityManager);
    }
    
    public function onKernelResponse(FilterResponseEvent $event) {
        $kernel    = $event->getKernel();
        $request   = $event->getRequest();
        $response  = $event->getResponse();
        
        if (strlen(strpos($request->getUri(), "cp_profile_credits_payPal")) > 0) {
            $settingRows = $this->utility->getQuery()->selectAllSettingsFromDatabase();
            
            $payPal = new PayPal(true, $settingRows['payPal_sandbox']);
            
            if ($payPal->ipn() == true) {
                $payPalElements = $payPal->getElements();

                $paymentRow = $this->utility->getQuery()->selectPaymentWithTransactionFromDatabase($payPalElements['txn_id']);

                if ($paymentRow == false) {
                    if (isset($payPalElements['payment_status']) == true) {
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

                            error_log("Completed: " . print_r($payPalElements, true));
                        }
                        else if ($payPalElements['payment_status'] == "Pending")
                            error_log("Pending: " . print_r($payPalElements, true));
                    }
                }
            }
        }
    }
    
    // Functions private
    private function updateCredits($payPalElements) {
        $userRow = $this->utility->getQuery()->selectUserFromDatabase("id", $payPalElements['custom']);
        
        $id = $userRow['id'];
        $credits = $userRow['credits'] + $payPalElements['quantity'];
        
        $connection = $this->entityManager->getConnection();
        
        $query = $connection->prepare("UPDATE users
                                        SET credits = :credits
                                        WHERE id = :id");
        
        $query->bindValue(":credits", $credits);
        $query->bindValue(":id", $id);
        
        $query->execute();
    }
}