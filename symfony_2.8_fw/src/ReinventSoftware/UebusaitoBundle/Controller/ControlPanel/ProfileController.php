<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use ReinventSoftware\UebusaitoBundle\Form\UserFormType;

use ReinventSoftware\UebusaitoBundle\Form\PasswordFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\PasswordModel;

use ReinventSoftware\UebusaitoBundle\Form\CreditsFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\CreditsModel;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

class ProfileController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    private $requestStack;
    private $translator;
    
    private $utility;
    private $ajax;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:control_panel/profile.html.twig")
     */
    public function indexAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        $usernameOld = "";
        
        // Create form
        $userFormType = new UserFormType($this->utility);
        $form = $this->createForm($userFormType, $this->getUser(), Array(
            'validation_groups' => Array(
                'profile'
            )
        ));
        
        if (in_array("ROLE_ADMIN", $this->utility->getQuery()->selectRoleLevelFromDatabase($this->getUser()->getRoleId())) == false)
            $form->remove("username");
        else
            $usernameOld = $this->getUser()->getUsername();
        
        $form->remove("roleId");
        $form->remove("password");
        $form->remove("notLocked");
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);
                
                // Check form
                if ($form->isValid() == true) {
                    rename("{$this->utility->getPathRootFull()}/src/ReinventSoftware/UebusaitoBundle/Resources/files/$usernameOld",
                            "{$this->utility->getPathRootFull()}/src/ReinventSoftware/UebusaitoBundle/Resources/files/{$form->get("username")->getData()}");
                    
                    // Insert in database
                    $this->entityManager->persist($this->getUser());
                    $this->entityManager->flush();
                    
                    $this->response['messages']['success'] = $this->translator->trans("profileController_1");
                }
                else {
                    $this->response['messages']['error'] = $this->translator->trans("profileController_2");
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
     * @Template("UebusaitoBundle:render:control_panel/profile_password.html.twig")
     */
    public function passwordAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        // Create form
        $passwordFormType = new PasswordFormType();
        $form = $this->createForm($passwordFormType, new PasswordModel(), Array(
            'validation_groups' => Array(
                'profile_password'
            )
        ));
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);
                
                // Check form
                if ($form->isValid() == true) {
                    $message = $this->utility->configureUserProfilePassword($this->getUser(), 1, $form);
                    
                    if ($message == "ok") {
                        // Insert in database
                        $this->entityManager->persist($this->getUser());
                        $this->entityManager->flush();
                        
                        $this->response['messages']['success'] = $this->translator->trans("profileController_3");
                    }
                    else
                        $this->response['messages']['error'] = $message;
                }
                else {
                    $this->response['messages']['error'] = $this->translator->trans("profileController_4");
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $currentCredits = $this->getUser() != null ? $this->getUser()->getCredits() : 0;
        $settingRows = $this->utility->getQuery()->selectAllSettingsFromDatabase();
        
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
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);
                
                // Check form
                if ($form->isValid() == true)
                    $this->response['messages']['success'] = $this->translator->trans("profileController_5");
                else {
                    $this->response['messages']['error'] = $this->translator->trans("profileController_6");
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
    
    // Functions private
}