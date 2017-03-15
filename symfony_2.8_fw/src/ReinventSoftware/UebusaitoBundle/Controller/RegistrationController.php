<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Entity\User;

use ReinventSoftware\UebusaitoBundle\Form\UserFormType;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

class RegistrationController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    private $requestStack;
    private $translator;
    
    private $utility;
    private $ajax;
    
    private $settings;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:registration.html.twig")
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
        
        $this->settings = $this->utility->getSettings();
        
        $this->response = Array();
        
        $user = new User();
        
        $userId = $this->utility->getQuery()->selectUserIdWithHelpCodeFromDatabase($this->urlExtra);
        
        if ($userId == null) {
            // Create form
            $userFormType = new UserFormType($this->utility);
            $form = $this->createForm($userFormType, $user, Array(
                'validation_groups' => Array(
                    'registration'
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
                        $message = $this->utility->configureUserProfilePassword($user, 2, $form);

                        if ($message == "ok") {
                            $this->utility->configureUserParameters($user);

                            $helpCode = $this->utility->generateRandomString();

                            $user->setDateRegistration(date("Y-m-d H:i:s"));
                            $user->setHelpCode($helpCode);

                            $url = $this->utility->getUrlRoot() . "/" . $this->requestStack->attributes->get("_locale") . "/" . $this->requestStack->attributes->get("urlCurrentPageId") . "/" . $helpCode;

                            // Insert in database
                            $this->entityManager->persist($user);
                            $this->entityManager->flush();
                            
                            $message = "";
                            
                            if ($this->settings['registration_user_confirm_admin'] == true)
                                $message = "<p>" . $this->translator->trans("registrationController_1") . "</p><a href=\"$url\">$url</a>";
                            else {
                                $message = "<p>" . $this->translator->trans("registrationController_2") . "</p>";
                                
                                // Send email to admin
                                $this->utility->sendEmail(
                                    $this->settings['email_admin'],
                                    $this->translator->trans("registrationController_3"),
                                    "<p>" . $this->translator->trans("registrationController_4") . "<b>" . $user->getUsername() . "</b>. " . $this->translator->trans("registrationController_5") . "</p>",
                                    $_SERVER['SERVER_ADMIN']
                                );
                            }
                            
                            // Send email to user
                            $this->utility->sendEmail(
                                $user->getEmail(),
                                $this->translator->trans("registrationController_3"),
                                $message,
                                $_SERVER['SERVER_ADMIN']
                            );
                            
                            mkdir("{$this->utility->getPathBundle()}/Resources/files/{$user->getUsername()}");

                            $this->response['messages']['success'] = $this->translator->trans("registrationController_6");
                        }
                        else
                            $this->response['messages']['error'] = $message;
                    }
                    else {
                        $this->response['messages']['error'] = $this->translator->trans("registrationController_7");
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
        }
        else {
            $user = $this->entityManager->getRepository("UebusaitoBundle:User")->find($userId);
            
            $user->setNotLocked(1);
            $user->setHelpCode(null);
            $user->setCredits(0);
            
            // Insert in database
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            $this->response['messages']['success'] = $this->translator->trans("registrationController_8");
            
            return Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response,
                'form' => null
            );
        }
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response,
            'form' => $form->createView()
        );
    }
    
    // Functions private
}