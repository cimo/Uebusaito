<?php
namespace App\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

use App\Classes\System\Utility;
use App\Classes\System\PayPal;

use App\Entity\Payment;

class PayPalIpnListener {
    // Vars
    private $container;
    private $entityManager;
    private $router;
    private $requestStack;
    
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager, Router $router, RequestStack $requestStack) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->requestStack = $requestStack;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
    }
    
    public function onKernelResponse(FilterResponseEvent $event) {
        $request = $event->getRequest();
        
        if (strpos($request->getUri(), "myPage_profile_credit_payPal") !== false) {
            $settingRow = $this->query->selectSettingDatabase();
            
            $payPal = new PayPal(true, false, $settingRow['payPal_sandbox']);
            
            if ($payPal->ipn() == true) {
                $payPalElements = $payPal->getElements();

                $paymentRow = $this->query->selectPaymentDatabase($payPalElements['txn_id']);

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

                        $this->updateCreditDatabase($payPalElements);
                        
                        // Insert in database
                        $this->entityManager->persist($payment);
                        $this->entityManager->flush();

                        error_log("Completed: " . print_r($payPalElements, true) . PHP_EOL);
                    }
                    else if ($payPalElements['payment_status'] == "Pending")
                        error_log("Pending: " . print_r($payPalElements, true) . PHP_EOL);
                }
            }
        }
    }
    
    // Functions private
    private function updateCreditDatabase($payPalElements) {
        $userRow = $this->query->selectUserDatabase($payPalElements['custom']);
        
        $id = $userRow['id'];
        $credit = $userRow['credit'] + $payPalElements['quantity'];
        
        $query = $this->utility->getConnection()->prepare("UPDATE users
                                                            SET credit = :credit
                                                            WHERE id = :id");
        
        $query->bindValue(":credit", $credit);
        $query->bindValue(":id", $id);
        
        $query->execute();
    }
}