<?php
namespace ReinventSoftware\UebusaitoBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UebusaitoUtility;

class RequestListener {
    // Vars
    private $container;
    private $entityManager;
    private $router;
    
    private $utility;
    private $uebusaitoUtility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager, Router $router) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->router = $router;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
    }
    
    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();
        
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType())
            return;
        
        $settingRow = $this->query->selectSettingDatabase();
        
        /*$urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        if (substr($request->getPathInfo(), strpos($request->getPathInfo(), "/") + 1) == "")
            $_SESSION['checkLocale'] = false;
        else
            $_SESSION['checkLocale'] = true;
        
        if ($_SESSION['checkLocale'] == false)
            $event->setResponse(new RedirectResponse($request->getUri() . $urlLocale));*/
        
        if ($settingRow['https'] == true) {
            if ($request->isSecure() == false) {
                $request->server->set("HTTPS", true);
                $request->server->set("SERVER_PORT", 443);
                
                $event->setResponse(new RedirectResponse($request->getUri()));
            }
        }
        else {
            if ($request->isSecure() == true) {
                $request->server->set("HTTPS", false);
                $request->server->set("SERVER_PORT", 80);
                
                $event->setResponse(new RedirectResponse($request->getUri()));
            }
        }
    }
    
    // Functions private
}