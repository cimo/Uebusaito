<?php
namespace ReinventSoftware\UebusaitoBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;

class ErrorListener {
    // Vars
    private $container;
    private $entityManager;
    private $router;
    
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager, Router $router) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->router = $router;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
    }
    
    public function onKernelException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();

        if ($exception instanceof NotFoundHttpException) {
            if ($event->getRequest()->get("_route") == null) {
                $settingRow = $this->query->selectSettingDatabase();
                
                $response = new RedirectResponse("{$this->utility->getUrlRoot()}{$this->utility->getWebsiteFile()}/{$settingRow['language']}?error=404");
                
                $event->setResponse($response);
            }
        }
    }
    
    // Functions private
}