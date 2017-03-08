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
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

class AuthenticationListener implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface, LogoutSuccessHandlerInterface {
    // Vars
    private $container;
    private $entityManager;
    
    private $tokenStorage;
    private $translator;
    
    private $utility;
    private $ajax;
    
    private $settings;
    
    private $response;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->translator = $this->container->get("translator");
        $this->tokenStorage = $this->container->get("security.token_storage");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->settings = $this->utility->getSettings();
        
        $this->response = Array();
    }
    
    public function onAuthenticationSuccess(Request $requestStack, TokenInterface $token) {
        $referer = $requestStack->headers->get("referer");
        
        if ($requestStack->isXmlHttpRequest() == true) {
            $user = $this->tokenStorage->getToken()->getUser();
            
            $settingRows = $this->utility->getQuery()->selectAllSettingsFromDatabase();
            $settingRoleIdExplode = explode(",", $settingRows['role_id']);
            array_pop($settingRoleIdExplode);
            
            $userRoleIdExplode = explode(",", $user->getRoleId());
            array_pop($userRoleIdExplode);
            
            if ($this->settings['active'] == true || ($this->settings['active'] == false && $this->utility->valueInSubArray($settingRoleIdExplode, $userRoleIdExplode) == true)) {
                $user->setDateLastLogin(date("Y-m-d H:i:s"));

                // Insert in database
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                $this->response['values']['url'] = $referer;
            }
            else {
                $this->utility->sessionDestroy($requestStack->getSession(), $this->tokenStorage);
                
                $this->response['messages']['error'] = $this->translator->trans("authenticationListener_1");
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
            $this->response['messages']['error'] = $this->ajax->errors($exception->getMessage());
            
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