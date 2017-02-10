<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;

class RootController extends Controller {
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
     * @Template("UebusaitoBundle:render:index.html.twig")
     */
    public function indexAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utility->generateToken();
        $this->utility->configureCookie("PHPSESSID", 0, isset($_SERVER['HTTPS']), true);
        
        $this->response = Array();
        
        $token = isset($_SESSION['token']) == true ? $_SESSION['token'] : "";
        $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
        $settings = $this->utility->getSettings();
        
        $this->response['session']['token'] = $token;
        $this->response['session']['activity'] = $sessionActivity;
        
        $this->response['path']['documentRoot'] = $_SERVER['DOCUMENT_ROOT'];
        $this->response['path']['root'] = $this->utility->getPathRoot();
        $this->response['path']['rootFull'] = $this->utility->getPathRootFull();
        $this->response['path']['bundle'] = "{$this->utility->getPathRootFull()}/src/ReinventSoftware/UebusaitoBundle";
        
        $this->response['url']['root'] = $this->utility->getUrlRoot();
        $this->response['url']['public'] = $this->utility->getUrlPublic();
        $this->response['url']['view'] = $this->utility->getUrlView();
        
        $this->response['modules']['header'] = $this->utility->getQuery()->selectAllModulesFromDatabase(null, "header");
        $this->response['modules']['left'] = $this->utility->getQuery()->selectAllModulesFromDatabase(null, "left");
        $this->response['modules']['center'] = $this->utility->getQuery()->selectAllModulesFromDatabase(null, "center");
        $this->response['modules']['right'] = $this->utility->getQuery()->selectAllModulesFromDatabase(null, "right");
        $this->response['modules']['footer'] = $this->utility->getQuery()->selectAllModulesFromDatabase(null, "footer");
        
        $this->get("twig")->addGlobal("websiteName", $this->utility->getWebsiteName());
        $this->get("twig")->addGlobal("settings", $settings);
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    // Functions private
}