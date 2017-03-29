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
            
            $settingRows = $this->query->selectAllSettingsFromDatabase();
            $settingRoleIdExplode = explode(",", $settingRows['role_id']);
            array_pop($settingRoleIdExplode);
            
            $userRoleIdExplode = explode(",", $user->getRoleId());
            array_pop($userRoleIdExplode);
            
            $attemptLogin = $this->utilityPrivate->attemptLogin("loginSuccess", $user->getId());
            
            if ($this->utility->getSettings()['active'] == true && $attemptLogin[0] == true ||
                    ($this->utility->getSettings()['active'] == false && $attemptLogin[0] == true && $this->utility->valueInSubArray($settingRoleIdExplode, $userRoleIdExplode) == true)) {
                $user->setDateLastLogin(date("Y-m-d H:i:s"));

                // Insert in database
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                $this->response['values']['url'] = $referer;
            }
            else {
                $languageText = isset($_SESSION['languageText']) == true ? $_SESSION['languageText'] : $this->utility->getSettings()['language'];
                
                $this->utility->sessionDestroy();
                
                $_SESSION['languageText'] = $languageText;
                
                if ($attemptLogin[0] == true)
                    $message = $this->utility->getTranslator()->trans("authenticationListener_1");
                else {
                    if ($attemptLogin[1] == "lock")
                        $message = $this->utility->getTranslator()->trans("authenticationListener_3") . $attemptLogin[2];
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
            
            $attemptLogin = $this->utilityPrivate->attemptLogin("loginFailure", $username);
            
            if ($attemptLogin[0] == true)
                $message = $this->utility->getTranslator()->trans("authenticationListener_2");
            else {
                if ($attemptLogin[1] == "lock")
                    $message = $this->utility->getTranslator()->trans("authenticationListener_3") . $attemptLogin[2];
                else if ($attemptLogin[1] == "try")
                    $message = $this->utility->getTranslator()->trans("authenticationListener_4") . "{$attemptLogin[2]} / " . $this->utility->getSettings()['login_attempt_count'];
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
        
        if ($requestStack->isXmlHttpRequest() == true) {
            $baseUrl = $requestStack->getBaseUrl();
            $parameters = $this->utility->urlParameters($referer, $baseUrl);
            $parameters = $this->utility->urlParametersControl($parameters);
            
            $this->response['values']['url'] = "$baseUrl/{$_SESSION['languageText']}/{$parameters[1]}/{$parameters[2]}";
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
        else
            return new RedirectResponse($referer);
    }
    
    // Functions private
}