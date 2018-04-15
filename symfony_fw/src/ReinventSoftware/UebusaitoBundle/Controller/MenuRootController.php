<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;

class MenuRootController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    /**
    * @Method({"GET"})
    * @Template("@UebusaitoBundleViews/render/module/menu_root.html.twig")
    */
    public function moduleAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        // Logic
        $moduleRow = $this->query->selectModuleDatabase(1);
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        $this->response['values']['url'] = "{$this->utility->getUrlRoot()}{$this->utility->getWebsiteFile()}/{$this->urlLocale}";
        $this->response['values']['pageList'] = $this->utility->createPageList($pageRows, false);
        $this->response['values']['moduleRows'] = $this->query->selectAllModuleDatabase();
        $this->response['values']['languageOption'] = $this->utility->createLanguageOptionHtml($this->urlLocale);
        
        if ($this->getUser() != null) {
            $this->response['values']['user'] = $this->getUser();
            $this->response['values']['dateLastLogin'] = $this->utility->dateFormat($this->getUser()->getDateLastLogin());
            $this->response['values']['roleUserRow'] = $this->query->selectRoleUserDatabase($this->getUser()->getRoleUserId(), true);
        }
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    // Functions private
}