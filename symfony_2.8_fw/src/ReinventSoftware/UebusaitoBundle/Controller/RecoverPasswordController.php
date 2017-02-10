<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Form\RecoverPasswordFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\RecoverPasswordModel;

use ReinventSoftware\UebusaitoBundle\Form\ChangePasswordFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\ChangePasswordModel;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

class RecoverPasswordController extends Controller {
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
     * @Template("UebusaitoBundle:render:recover_password.html.twig")
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
        
        $userId = $this->utility->getQuery()->selectUserIdWithHelpCodeFromDatabase($this->urlExtra);
        
        if ($userId == null) {
            $this->response['values']['userId'] = $userId;
            
            // Create form
            $recoverPasswordFormType = new RecoverPasswordFormType();
            $formRecoverPassword = $this->createForm($recoverPasswordFormType, new RecoverPasswordModel(), Array(
                'validation_groups' => Array(
                    'recover_password'
                )
            ));
            
            // Request post
            if ($this->requestStack->getMethod() == "POST") {
                $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);

                if ($sessionActivity != "")
                    $this->response['session']['activity'] = $sessionActivity;
                else {
                    $formRecoverPassword->handleRequest($this->requestStack);

                    // Check form
                    if ($formRecoverPassword->isValid() == true) {
                        $email = $formRecoverPassword->get("email")->getData();

                        $user = $this->entityManager->getRepository("UebusaitoBundle:User")->loadUserByUsername($email);

                        if ($user != null) {
                            $helpCode = $this->utility->generateRandomString();

                            $user->setHelpCode($helpCode);

                            $url = $this->utility->getUrlRoot() . "/" . $this->requestStack->attributes->get("_locale") . "/" . $this->requestStack->attributes->get("urlCurrentPageId") . "/" . $helpCode;

                            // Insert in database
                            $this->entityManager->persist($user);
                            $this->entityManager->flush();

                            // Send email to user
                            $this->utility->sendEmail($user->getEmail(),
                                                        "Recover password",
                                                        "<p>Click on this link for reset your password:</p>" .
                                                        "<a href=\"$url\">$url</a>",
                                                        $_SERVER['SERVER_ADMIN']);

                            $this->response['messages']['success'] = $this->translator->trans("recoverPasswordController_1");
                        }
                        else
                            $this->response['messages']['error'] = $this->translator->trans("recoverPasswordController_2");
                    }
                    else {
                        $this->response['errors'] = $this->ajax->errors($formRecoverPassword);
                        $this->response['messages']['error'] = $this->translator->trans("recoverPasswordController_3");
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
            
            $this->response['values']['userId'] = $user->getId();
            
            // Create form
            $changePasswordFormType = new ChangePasswordFormType();
            $formChangePassword = $this->createForm($changePasswordFormType, new ChangePasswordModel(), Array(
                'validation_groups' => Array(
                    'change_password'
                )
            ));
            
            // Request post
            if ($this->requestStack->getMethod() == "POST") {
                $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);

                if ($sessionActivity != "")
                    $this->response['session']['activity'] = $sessionActivity;
                else {
                    $formChangePassword->handleRequest($this->requestStack);

                    // Check form
                    if ($formChangePassword->isValid() == true) {
                        $message = $this->utility->configureUserProfilePassword($user, 2, $formChangePassword);

                        if ($message == "ok") {
                            $user->setHelpCode(null);

                            // Insert in database
                            $this->entityManager->persist($user);
                            $this->entityManager->flush();

                            $this->response['messages']['success'] = $this->translator->trans("recoverPasswordController_4");
                        }
                        else
                            $this->response['messages']['error'] = $message;
                    }
                    else {
                        $this->response['errors'] = $this->ajax->errors($formChangePassword);
                        $this->response['messages']['error'] = $this->translator->trans("recoverPasswordController_5");
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
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response,
            'form' => $formRecoverPassword->createView()
        );
    }
    
    // Functions private
}