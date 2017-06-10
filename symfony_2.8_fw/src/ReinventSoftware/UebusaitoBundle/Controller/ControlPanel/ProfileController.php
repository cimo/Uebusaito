<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Upload;

use ReinventSoftware\UebusaitoBundle\Form\UserFormType;

use ReinventSoftware\UebusaitoBundle\Form\PasswordFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\PasswordModel;

use ReinventSoftware\UebusaitoBundle\Form\CreditsFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\CreditsModel;

class ProfileController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $utility;
    private $utilityPrivate;
    private $query;
    private $ajax;
    private $upload;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:control_panel/profile.html.twig")
     */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $usernameOld = $this->getUser()->getUsername();
        
        $avatar = "{$this->utility->getUrlWebBundle()}/files/$usernameOld/Avatar.jpg";
        
        $this->response = Array();
        
        // Create form
        $userFormType = new UserFormType();
        $form = $this->createForm($userFormType, $this->getUser(), Array(
            'validation_groups' => Array(
                'profile'
            )
        ));
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        if ($chekRoleLevel == false)
            $form->remove("username");
        
        $form->remove("roleId");
        $form->remove("password");
        $form->remove("notLocked");
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_USER"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->utility->getRequestStack());
                
                // Check form
                if ($form->isValid() == true) {
                    if (file_exists("{$this->utility->getPathSrcBundle()}/Resources/files/$usernameOld") == true)
                        rename("{$this->utility->getPathSrcBundle()}/Resources/files/$usernameOld",
                                "{$this->utility->getPathSrcBundle()}/Resources/files/{$form->get("username")->getData()}");
                    
                    if (file_exists("{$this->utility->getPathSrcBundle()}/Resources/public/files/$usernameOld") == true)
                        rename("{$this->utility->getPathSrcBundle()}/Resources/public/files/$usernameOld",
                                "{$this->utility->getPathSrcBundle()}/Resources/public/files/{$form->get("username")->getData()}");
                    
                    if (file_exists("{$this->utility->getPathWebBundle()}/files/$usernameOld") == true)
                        rename("{$this->utility->getPathWebBundle()}/files/$usernameOld",
                                "{$this->utility->getPathWebBundle()}/files/{$form->get("username")->getData()}");
                    
                    // Insert in database
                    $this->entityManager->persist($this->getUser());
                    $this->entityManager->flush();
                    
                    if ($form->get("username")->getData() != $usernameOld)
                        $this->response['action']['refresh'] = true;
                    else
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("profileController_1");
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("profileController_2");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
            }
            
            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response,
                'avatar' => $avatar
            ));
        }
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response,
            'form' => $form->createView(),
            'avatar' => $avatar
        );
    }
    
    /**
     * @Template("UebusaitoBundle:render:control_panel/profile_password.html.twig")
     */
    public function passwordAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        // Create form
        $passwordFormType = new PasswordFormType();
        $form = $this->createForm($passwordFormType, new PasswordModel(), Array(
            'validation_groups' => Array(
                'profile_password'
            )
        ));
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_USER"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->utility->getRequestStack());
                
                // Check form
                if ($form->isValid() == true) {
                    $message = $this->utilityPrivate->configureUserProfilePassword("withOld", $this->getUser(), $form);
                    
                    if ($message == "ok") {
                        // Insert in database
                        $this->entityManager->persist($this->getUser());
                        $this->entityManager->flush();
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("profileController_3");
                    }
                    else
                        $this->response['messages']['error'] = $message;
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("profileController_4");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
            }
            
            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response
            ));
        }
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response,
            'form' => $form->createView()
        );
    }
    
    /**
     * @Template("UebusaitoBundle:render:control_panel/profile_credits.html.twig")
     */
    public function creditsAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $currentCredits = $this->getUser() != null ? $this->getUser()->getCredits() : 0;
        $settingRows = $this->query->selectAllSettingsDatabase();
        
        $this->response = Array();
        
        // Create form
        $creditsFormType = new CreditsFormType();
        $form = $this->createForm($creditsFormType, new CreditsModel(), Array(
            'validation_groups' => Array(
                'profile_credits'
            )
        ));
        
        $this->response['values']['currentCredits'] = $currentCredits;
        $this->response['values']['payPalSandbox'] = $settingRows['payPal_sandbox'];
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_USER"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->utility->getRequestStack());
                
                // Check form
                if ($form->isValid() == true)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("profileController_5");
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("profileController_6");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
            }

            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response
            ));
        }
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response,
            'form' => $form->createView()
        );
    }
    
    public function creditsPayPalAction($_locale, $urlCurrentPageId, $urlExtra) {
        return new Response();
    }
    
    public function uploadAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->upload = new Upload($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
        
        if ($sessionActivity != "")
            $this->response['session']['activity'] = $sessionActivity;
        else {
            $path = "";
            
            if ($this->getUser() != null)
                $path = "{$this->utility->getPathSrcBundle()}/Resources/public/files/{$this->getUser()->getUsername()}";
            
            $this->response['upload']['inputType'] = "single";
            $this->response['upload']['maxSize'] = 0;
            $this->response['upload']['chunkSize'] = 1000000;
            $this->response['upload']['extensions'] = Array('jpeg', 'jpg', 'png', 'gif');
            
            $this->response['upload']['processFile'] = $this->upload->processFile(
                $path,
                $this->response['upload']['inputType'],
                $this->response['upload']['maxSize'],
                $this->response['upload']['chunkSize'],
                $this->response['upload']['extensions']
            );
        }
        
        return $this->ajax->response(Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        ));
    }
    
    // Functions private
}