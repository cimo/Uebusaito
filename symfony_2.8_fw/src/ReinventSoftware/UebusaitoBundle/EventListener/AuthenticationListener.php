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
    
    private $utility;
    private $utilityPrivate;
    private $query;
    private $ajax;
    
    private $response;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
    }
    
    public function onAuthenticationSuccess(Request $requestStack, TokenInterface $token) {
        $referer = $requestStack->headers->get("referer");
        
        if ($requestStack->isXmlHttpRequest() == true) {
            $user = $this->utility->getTokenStorage()->getToken()->getUser();
            $settingRows = $this->query->selectAllSettingsDatabase();
            
            $checkCaptcha = $this->utilityPrivate->checkCaptcha($requestStack->request->get("captcha"));
            $checkAttemptLogin = $this->utilityPrivate->checkAttemptLogin("success", $user->getId());
            $checkRoles = $this->utilityPrivate->checkRoles($settingRows['role_id'], $user->getRoleId());
            
            if ($checkCaptcha == true &&
                    (($this->utility->getSettings()['active'] == true && $checkAttemptLogin[0] == true) ||
                    ($this->utility->getSettings()['active'] == false && $checkAttemptLogin[0] == true && $checkRoles == true))) {
                $user->setDateLastLogin(date("Y-m-d H:i:s"));

                // Update in database
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->response['values']['url'] = $referer;
            }
            else {
                $this->container->get("security.context")->setToken(null);
                
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

    public function onAuthenticationFailure(Request $requestStack, AuthenticationException $exception) {
        $referer = $requestStack->headers->get("referer");
        
        if ($requestStack->isXmlHttpRequest() == true) {
            $username = $requestStack->request->get("_username");
            
            $checkCaptcha = $this->utilityPrivate->checkCaptcha($requestStack->request->get("captcha"));
            $checkAttemptLogin = $this->utilityPrivate->checkAttemptLogin("failure", $username);
            
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
                        $message = $this->utility->getTranslator()->trans("authenticationListener_4") . "{$checkAttemptLogin[2]} / " . $this->utility->getSettings()['login_attempt_count'];
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
    
    public function onLogoutSuccess(Request $requestStack) {
        $referer = $requestStack->headers->get("referer");
        $baseUrl = $requestStack->getBaseUrl();
        $parameters = $this->utility->urlParameters($referer, $baseUrl);
        $parameters = $this->utilityPrivate->controlUrlParameters($parameters);
        
        $url = "$baseUrl/{$_SESSION['language_text']}/{$parameters[1]}/{$parameters[2]}";
        
        if ($requestStack->isXmlHttpRequest() == true) {
            $this->response['values']['url'] = $url;
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
        else
            return new RedirectResponse($referer);
    }
    
    // Functions private
}