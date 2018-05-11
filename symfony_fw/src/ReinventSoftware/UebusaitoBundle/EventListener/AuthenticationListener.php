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

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Captcha;

class AuthenticationListener implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface, LogoutSuccessHandlerInterface {
    // Vars
    private $container;
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    private $captcha;
    
    private $settingRow;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->captcha = new Captcha($this->container, $this->entityManager);
        
        $this->settingRow = $this->query->selectSettingDatabase();
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        $referer = $request->headers->get("referer");
        
        if ($request->isXmlHttpRequest() == true) {
            $user = $token->getUser();
            
            $checkCaptcha = $this->captcha->check($this->settingRow['captcha'], $request->get("captcha"));
            $checkAttemptLogin = $this->utility->checkAttemptLogin("success", $user->getId(), $this->settingRow, $user);
            $arrayExplodeFindValue = $this->utility->arrayExplodeFindValue($this->settingRow['role_user_id'], $user->getRoleUserId());
            
            if ($checkCaptcha == true && (($this->settingRow['website_active'] == true && $checkAttemptLogin[0] == true)
                || ($this->settingRow['website_active'] == false && $checkAttemptLogin[0] == true && $arrayExplodeFindValue == true)))
                $this->response['values']['url'] = $referer;
            else {
                $this->utility->getTokenStorage()->setToken(null);
                
                if ($checkCaptcha == false) {
                    $message = $this->utility->getTranslator()->trans("captcha_1");
                    
                    $this->response['values']['captchaReload'] = true;
                }
                else {
                    if ($checkAttemptLogin[0] == true)
                        $message = $this->utility->getTranslator()->trans("authenticationListener_1");
                    else {
                        if ($checkAttemptLogin[1] == "lock")
                            $message = $this->utility->getTranslator()->trans("authenticationListener_4a") . $checkAttemptLogin[2] . $this->utility->getTranslator()->trans("authenticationListener_4b");
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
            
            if ($this->utility->checkUserNotLocked($username) == false)
                $message = $this->utility->getTranslator()->trans("authenticationListener_2");
            else {
                $checkCaptcha = $this->captcha->check($this->settingRow['captcha'], $request->get("captcha"));
                $checkAttemptLogin = $this->utility->checkAttemptLogin("failure", $username, $this->settingRow);

                if ($checkCaptcha == true && $checkAttemptLogin[0] == true)
                    $message = $this->utility->getTranslator()->trans("authenticationListener_3");
                else {
                    if ($checkCaptcha == false) {
                        $message = $this->utility->getTranslator()->trans("captcha_1");

                        $this->response['values']['captchaReload'] = true;
                    }
                    else {
                        if ($checkAttemptLogin[1] == "lock")
                            $message = $this->utility->getTranslator()->trans("authenticationListener_4a") . $checkAttemptLogin[2] . $this->utility->getTranslator()->trans("authenticationListener_4b");
                        else if ($checkAttemptLogin[1] == "try")
                            $message = $this->utility->getTranslator()->trans("authenticationListener_5") . "{$checkAttemptLogin[2]} / {$this->settingRow['login_attempt_count']}";
                    }
                }
            }
            
            $this->response['messages']['error'] = $message;
            $this->response['errors'] = Array(
                "username" => Array(
                    ""
                ),
                "password" => Array(
                    ""
                )
            );
            
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