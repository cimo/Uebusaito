<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

use ReinventSoftware\UebusaitoBundle\Form\RecoverPasswordFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\RecoverPasswordModel;

use ReinventSoftware\UebusaitoBundle\Form\ChangePasswordFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\ChangePasswordModel;

class RecoverPasswordController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $utility;
    private $utilityPrivate;
    private $query;
    private $ajax;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:recover_password.html.twig")
     */
    public function recoverPasswordAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $userRow = $this->query->selectUserWithHelpCodeDatabase($this->urlExtra);
        
        if ($userRow['id'] == null) {
            $this->response['values']['userId'] = $userRow['id'];
            
            // Create form
            $recoverPasswordFormType = new RecoverPasswordFormType();
            $formRecoverPassword = $this->createForm($recoverPasswordFormType, new RecoverPasswordModel(), Array(
                'validation_groups' => Array(
                    'recover_password'
                )
            ));
            
            // Request post
            if ($this->utility->getRequestStack()->getMethod() == "POST") {
                $sessionActivity = $this->utilityPrivate->checkSessionOverTime();

                if ($sessionActivity != "")
                    $this->response['session']['activity'] = $sessionActivity;
                else {
                    $formRecoverPassword->handleRequest($this->utility->getRequestStack());

                    // Check form
                    if ($formRecoverPassword->isValid() == true) {
                        $email = $formRecoverPassword->get("email")->getData();

                        $user = $this->entityManager->getRepository("UebusaitoBundle:User")->loadUserByUsername($email);

                        if ($user != null) {
                            $helpCode = $this->utility->generateRandomString(20);

                            $user->setHelpCode($helpCode);

                            $url = $this->utility->getUrlRoot() . "/" . $this->utility->getRequestStack()->attributes->get("_locale") . "/" . $this->utility->getRequestStack()->attributes->get("urlCurrentPageId") . "/" . $helpCode;

                            // Send email to user
                            $this->utility->sendEmail($user->getEmail(),
                                                        "Recover password",
                                                        "<p>Click on this link for reset your password:</p>" .
                                                        "<a href=\"$url\">$url</a>",
                                                        $_SERVER['SERVER_ADMIN']);
                            
                            // Insert in database
                            $this->entityManager->persist($user);
                            $this->entityManager->flush();
                            
                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("recoverPasswordController_1");
                        }
                        else
                            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("recoverPasswordController_2");
                    }
                    else {
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("recoverPasswordController_3");
                        $this->response['errors'] = $this->ajax->errors($formRecoverPassword);
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
            $user = $this->entityManager->getRepository("UebusaitoBundle:User")->find($userRow['id']);
            
            $this->response['values']['userId'] = $user->getId();
            
            // Create form
            $changePasswordFormType = new ChangePasswordFormType();
            $formChangePassword = $this->createForm($changePasswordFormType, new ChangePasswordModel(), Array(
                'validation_groups' => Array(
                    'change_password'
                )
            ));
            
            // Request post
            if ($this->utility->getRequestStack()->getMethod() == "POST") {
                $sessionActivity = $this->utilityPrivate->checkSessionOverTime();

                if ($sessionActivity != "")
                    $this->response['session']['activity'] = $sessionActivity;
                else {
                    $formChangePassword->handleRequest($this->utility->getRequestStack());

                    // Check form
                    if ($formChangePassword->isValid() == true) {
                        $message = $this->utilityPrivate->configureUserProfilePassword("withoutOld", $user, $formChangePassword);

                        if ($message == "ok") {
                            $user->setHelpCode(null);

                            // Insert in database
                            $this->entityManager->persist($user);
                            $this->entityManager->flush();

                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("recoverPasswordController_4");
                        }
                        else
                            $this->response['messages']['error'] = $message;
                    }
                    else {
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("recoverPasswordController_5");
                        $this->response['errors'] = $this->ajax->errors($formChangePassword);
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