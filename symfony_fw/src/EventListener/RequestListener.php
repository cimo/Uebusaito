<?php
namespace App\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Classes\System\Utility;

class RequestListener {
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
    
    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();
        
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType())
            return;
        
        $settingRow = $this->query->selectSettingDatabase();
        
        $url = $this->utility->checkSessionOverTime($request, false, $this->router);
        
        if ($url != "")
            $event->setResponse(new RedirectResponse($url));
        
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