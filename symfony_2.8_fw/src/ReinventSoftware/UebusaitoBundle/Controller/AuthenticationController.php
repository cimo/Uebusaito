<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;

use ReinventSoftware\UebusaitoBundle\Form\AuthenticationFormType;

class AuthenticationController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $utility;
    private $utilityPrivate;
    private $query;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:module/authentication.html.twig")
     */
    public function indexAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        
        $this->response = Array();
        
        $moduleRow = $this->query->selectModuleFromDatabase(2);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        // Create form
        $authenticationFormType = new AuthenticationFormType();
        $form = $this->createForm($authenticationFormType, null);
        
        if ($this->utility->getAuthorizationChecker()->isGranted("IS_AUTHENTICATED_FULLY") == true) {
            $this->utilityPrivate->assignUserRole($this->getUser());
            
            $this->response['values']['user'] = $this->getUser();
            $this->response['values']['roleLevel'] = $this->query->selectUserRoleLevelFromDatabase($this->getUser()->getRoleId(), true);
            
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
    
    public function enterCheckAction() {
        // Empty is normal!
    }
    
    public function exitCheckAction() {
        // Empty is normal!
    }
    
    // Functions private
}