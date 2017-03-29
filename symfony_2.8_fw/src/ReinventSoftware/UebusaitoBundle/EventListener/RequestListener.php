<?php
namespace ReinventSoftware\UebusaitoBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;

class RequestListener {
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
    
    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();
        
        $completeUrl = $this->utility->getRequestStack()->getUri();
        $baseUrl = $this->utility->getRequestStack()->getBaseUrl();
        $parameters = $this->utility->urlParameters($completeUrl, $baseUrl);
        $parameters = $this->utility->urlParametersControl($parameters);
        
        $request->setLocale($parameters[0]);
        
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType())
            return;
        
        if ($this->utility->getSettings()['https'] == true) {
            if ($this->utility->getRequestStack()->isSecure() == false) {
                $this->utility->getRequestStack()->server->set("HTTPS", true);
                $this->utility->getRequestStack()->server->set("SERVER_PORT", 443);
                
                $event->setResponse(new RedirectResponse($this->utility->getRequestStack()->getUri()));
            }
        }
        else {
            if ($this->utility->getRequestStack()->isSecure() == true) {
                $this->utility->getRequestStack()->server->set("HTTPS", false);
                $this->utility->getRequestStack()->server->set("SERVER_PORT", 80);
                
                $event->setResponse(new RedirectResponse($this->utility->getRequestStack()->getUri()));
            }
        }
    }
    
    // Functions private
}