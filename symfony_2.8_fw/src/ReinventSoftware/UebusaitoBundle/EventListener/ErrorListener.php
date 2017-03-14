<?php
namespace ReinventSoftware\UebusaitoBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;

class ErrorListener {
    // Vars
    private $container;
    private $entityManager;
    
    private $requestStack;
    
    private $utility;
    
    private $router;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager, Router $router) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->requestStack = $this->container->get("request_stack")->getCurrentRequest();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        
        $this->router = $router;
    }
    
    public function onKernelException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();

        if ($exception instanceof NotFoundHttpException) {
            if ($event->getRequest()->get("_route") == null) {
                $url = $this->router->generate("error");
                $response = new RedirectResponse($url);
                $event->setResponse($response);
            }
        }
    }
    
    // Functions private
}