<?php
namespace ReinventSoftware\UebusaitoBundle\EventListener;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\RedirectResponse;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

class AuthenticationListener implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface, LogoutSuccessHandlerInterface {
    // Vars
    private $container;
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $utilityPrivate;
    private $query;
    private $ajax;
    
    private $settingRows;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->settingRows = $this->query->selectAllSettingsDatabase();
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        $referer = $request->headers->get("referer");
        
        if ($request->isXmlHttpRequest() == true) {
            $user = $token->getUser();
            
            $checkCaptcha = $this->utilityPrivate->checkCaptcha($this->settingRows, $request->get("captcha"));
            $checkAttemptLogin = $this->utilityPrivate->checkAttemptLogin("success", $user->getId(), $this->settingRows);
            $checkInRoles = $this->utilityPrivate->checkInRoles($this->settingRows['role_id'], $user->getRoleId());
            
            if ($checkCaptcha == true && (($this->settingRows['active'] == true && $checkAttemptLogin[0] == true) || ($this->settingRows['active'] == false && $checkAttemptLogin[0] == true && $checkInRoles == true)))
                $this->response['values']['url'] = $referer;
            else {
                $token->setToken(null);
                
                if ($checkCaptcha == false) {
                    $message = $this->utility->getTranslator()->trans("captcha_1");
                    
                    $this->response['values']['captchaReload'] = true;
                }
                else {
                    if ($checkAttemptLogin[0] == true)
                        $message = $this->utility->getTranslator()->trans("authenticationListener_1");
                    else {
                        if ($checkAttemptLogin[1] == "lock")
                            $message = $this->utility->getTranslator()->trans("authenticationListener_3a") . $checkAttemptLogin[2] . $this->utility->getTranslator()->trans("authenticationListener_3b");
                    }
                }

                $this->response['messages']['error'] = $message;
            }
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
        else
            return new RedirectResponse($referer);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        $referer = $request->headers->get("referer");
        
        if ($request->isXmlHttpRequest() == true) {
            $username = $request->get("_username");
            
            $checkCaptcha = $this->utilityPrivate->checkCaptcha($this->settingRows, $request->get("captcha"));
            $checkAttemptLogin = $this->utilityPrivate->checkAttemptLogin("failure", $username, $this->settingRows);
            
            if ($checkCaptcha == true && $checkAttemptLogin[0] == true)
                $message = $this->utility->getTranslator()->trans("authenticationListener_2");
            else {
                if ($checkCaptcha == false) {
                    $message = $this->utility->getTranslator()->trans("captcha_1");
                    
                    $this->response['values']['captchaReload'] = true;
                }
                else {
                    if ($checkAttemptLogin[1] == "lock")
                        $message = $this->utility->getTranslator()->trans("authenticationListener_3a") . $checkAttemptLogin[2] . $this->utility->getTranslator()->trans("authenticationListener_3b");
                    else if ($checkAttemptLogin[1] == "try")
                        $message = $this->utility->getTranslator()->trans("authenticationListener_4") . "{$checkAttemptLogin[2]} / " . $this->settingRows['login_attempt_count'];
                }
            }
            
            $this->response['messages']['error'] = $message;
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
        else
            return new RedirectResponse($referer);
    }
    
    public function onLogoutSuccess(Request $request) {
        $referer = $request->headers->get("referer");
        
        if ($request->isXmlHttpRequest() == true) {
            $this->response['values']['url'] = $referer;
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
        else
            return new RedirectResponse($referer);
    }
    
    // Functions private
}