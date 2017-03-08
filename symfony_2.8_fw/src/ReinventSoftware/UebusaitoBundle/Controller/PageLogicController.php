<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;

class PageLogicController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    private $translator;
    private $authorizationChecker;
    
    private $utility;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:module/page_logic.html.twig")
     */
    public function indexAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = ($urlCurrentPageId <= 0 || empty($urlCurrentPageId) == true) ? 2 : $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->translator = $this->get("translator");
        $this->authorizationChecker = $this->get("security.authorization_checker");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $moduleRow = $this->utility->getQuery()->selectModuleFromDatabase(3);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        $pageRow = $this->utility->getQuery()->selectPageFromDatabase($this->urlLocale, $this->urlCurrentPageId);
        
        $this->response['values']['controllerAction'] = null;
        $this->response['values']['title'] = $this->translator->trans("pageLogicController_1");
        $this->response['values']['argument'] = $this->translator->trans("pageLogicController_2");
        
        if ($pageRow != false) {
            $this->response['values']['controllerAction'] = $pageRow['controller_action'];
            $this->response['values']['title'] = $pageRow['title'];
            $this->response['values']['argument'] = $pageRow['argument'];
            
            if ($this->authorizationChecker->isGranted("IS_AUTHENTICATED_FULLY") == true) {
                if ($pageRow['protected'] == false && ($pageRow['id'] == 3 || $pageRow['id'] == 4)) {
                    // Page not available with login
                    $this->response['values']['controllerAction'] = null;
                    $this->response['values']['argument'] = $this->translator->trans("pageLogicController_3");

                    return Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $pageRow['id'],
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    );
                }
                else if ($pageRow['protected'] == true) {
                    $userRoleLevelRow = $this->utility->getQuery()->selectUserRoleLevelFromDatabase($this->getUser()->getRoleId());
                    
                    $pageRoleIdExplode = explode(",", $pageRow['role_id']);
                    array_pop($pageRoleIdExplode);

                    $userRoleIdExplode =  explode(",", $this->getUser()->getRoleId());
                    array_pop($userRoleIdExplode);
                            
                    if (in_array("ROLE_ADMIN", $userRoleLevelRow) == false && $this->utility->valueInSubArray($pageRoleIdExplode, $userRoleIdExplode) == false) {
                        // Page not available for role
                        $this->response['values']['controllerAction'] = null;
                        $this->response['values']['argument'] = $this->translator->trans("pageLogicController_4");

                        return Array(
                            'urlLocale' => $this->urlLocale,
                            'urlCurrentPageId' => $pageRow['id'],
                            'urlExtra' => $this->urlExtra,
                            'response' => $this->response
                        );
                    }
                    else {
                        // Page normal
                        return Array(
                            'urlLocale' => $this->urlLocale,
                            'urlCurrentPageId' => $pageRow['id'],
                            'urlExtra' => $this->urlExtra,
                            'response' => $this->response
                        );
                    }
                }
            }
            else {
                if ($pageRow['protected'] == false) {
                    // Page normal
                    return Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $pageRow['id'],
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    );
                }
                else {
                    // Page accessible only with login
                    $this->response['values']['controllerAction'] = null;
                    $this->response['values']['argument'] = $this->translator->trans("pageLogicController_5");
                    
                    return Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $pageRow['id'],
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    );
                }
            }
            
            return $this->pageDoesNotExist();
        }
        else
            return $this->pageDoesNotExist();
    }
    
    // Functions private
    private function pageDoesNotExist() {
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => 0,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
}