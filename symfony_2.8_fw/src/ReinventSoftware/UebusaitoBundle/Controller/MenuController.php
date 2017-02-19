<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;

class MenuController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    private $requestStack;
    
    private $utility;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:module/menu_root.html.twig")
     */
    public function indexAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        
        $moduleRow = $this->utility->getQuery()->selectModuleFromDatabase(1);
        $pageRows = $this->utility->getQuery()->selectAllPagesFromDatabase($this->urlLocale);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        $this->response['values']['url'] = $this->utility->getUrlRoot() . "/" . $this->requestStack->attributes->get("_locale");
        $this->response['values']['pagesList'] = $this->utility->createPagesList($pageRows, false);
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    // Functions private
}