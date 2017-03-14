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
    
    private $requestStack;
    
    private $utility;
    
    private $settings;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->requestStack = $this->container->get("request_stack")->getCurrentRequest();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        
        $this->settings = $this->utility->getSettings();
    }
    
    public function onKernelRequest(GetResponseEvent $event) {
        //$kernel = $event->getKernel();
        $request = $event->getRequest();
        
        $completeUrl = $this->requestStack->getUri();
        $baseUrl = $this->requestStack->getBaseUrl();
        $parameters = $this->utility->urlParameters($completeUrl, $baseUrl);
        $parameters = $this->utility->urlParametersControl($parameters);
        
        $request->setLocale($parameters[0]);
        
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType())
            return;
        
        if ($this->settings['https'] == true) {
            if ($this->requestStack->isSecure() == false) {
                $this->requestStack->server->set("HTTPS", true);
                $this->requestStack->server->set("SERVER_PORT", 443);
                
                $event->setResponse(new RedirectResponse($this->requestStack->getUri()));
            }
        }
        else {
            if ($this->requestStack->isSecure() == true) {
                $this->requestStack->server->set("HTTPS", false);
                $this->requestStack->server->set("SERVER_PORT", 80);
                
                $event->setResponse(new RedirectResponse($this->requestStack->getUri()));
            }
        }
    }
    
    // Functions private
}