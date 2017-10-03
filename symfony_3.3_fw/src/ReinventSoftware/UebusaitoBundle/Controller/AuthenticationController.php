<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UebusaitoUtility;

use ReinventSoftware\UebusaitoBundle\Form\AuthenticationFormType;

class AuthenticationController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $uebusaitoUtility;
    private $query;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "authentication",
    *   path = "/authentication/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/module/authentication.html.twig")
    */
    public function moduleAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $moduleRow = $this->query->selectModuleDatabase(2);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        // Form
        $form = $this->createForm(AuthenticationFormType::class, null, Array());
        $form->handleRequest($request);
        
        if ($this->utility->getAuthorizationChecker()->isGranted("IS_AUTHENTICATED_FULLY") == true) {
            $this->uebusaitoUtility->assignUserRole($this->getUser());

            $this->response['values']['user'] = $this->getUser();
            $this->response['values']['roleLevel'] = $this->query->selectUserRoleLevelDatabase($this->getUser()->getRoleId(), true);

            return Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response,
                'form' => null
            );
        }
        else
            $this->response['messages']['error'] = $this->utility->getAuthenticationUtils()->getLastAuthenticationError();
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response,
            'form' => $form->createView()
        );
    }
    
    /**
    * @Route(
    *   name = "authentication_enter_check",
    *   path = "/authentication_enter_check/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    */
    public function enterCheckAction() {
        // Empty is normal!
    }
    
    /**
    * @Route(
    *   name = "authentication_exit_check",
    *   path = "/authentication_exit_check/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"GET", "POST"})
    */
    public function exitCheckAction() {
        // Empty is normal!
    }
    
    // Functions private
}