<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Form\AuthenticationFormType;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;

class AuthenticationController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    private $translator;
    private $authenticationUtils;
    private $authorizationChecker;
    
    private $utility;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:authentication.html.twig")
     */
    public function indexAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->translator = $this->get("translator");
        $this->authenticationUtils = $this->get("security.authentication_utils");
        $this->authorizationChecker = $this->get("security.authorization_checker");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $moduleRow = $this->utility->getQuery()->selectModuleFromDatabase(2);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        // Create form
        $authenticationFormType = new AuthenticationFormType();
        $form = $this->createForm($authenticationFormType, null);
        
        if ($this->authorizationChecker->isGranted("IS_AUTHENTICATED_FULLY") == true) {
            $this->utility->assignUserRole($this->getUser());
            
            $this->response['values']['user'] = $this->getUser();
            $this->response['values']['roleLevel'] = $this->utility->getQuery()->selectRoleLevelFromDatabase($this->getUser()->getRoleId(), true);
            
            return Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response,
                'form' => null
            );
        }
        else
            $this->response['messages']['error'] = $this->authenticationUtils->getLastAuthenticationError();
        
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