<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;

class PageLogicController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $utility;
    private $query;
    
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
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        
        $this->response = Array();
        
        $moduleEntity = $this->entityManager->getRepository("UebusaitoBundle:Module")->find(3);
        
        $this->response['module']['id'] = $moduleEntity->getId();
        $this->response['module']['label'] = $moduleEntity->getLabel();
        
        $pageRow = $this->query->selectPageFromDatabase($this->urlLocale, $this->urlCurrentPageId);
        
        $this->response['values']['controllerAction'] = null;
        $this->response['values']['title'] = $this->utility->getTranslator()->trans("pageLogicController_1");
        $this->response['values']['argument'] = $this->utility->getTranslator()->trans("pageLogicController_2");
        
        if ($pageRow != false) {
            $this->response['values']['controllerAction'] = $pageRow['controller_action'];
            $this->response['values']['title'] = $pageRow['title'];
            $this->response['values']['argument'] = $pageRow['argument'];
            
            if ($this->utility->getAuthorizationChecker()->isGranted("IS_AUTHENTICATED_FULLY") == true) {
                if ($pageRow['protected'] == false && ($pageRow['id'] == 3 || $pageRow['id'] == 4)) {
                    // Page not available with login
                    $this->response['values']['controllerAction'] = null;
                    $this->response['values']['argument'] = $this->utility->getTranslator()->trans("pageLogicController_3");

                    return Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $pageRow['id'],
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    );
                }
                else if ($pageRow['protected'] == true) {
                    $chekRoleLevel = $this->utilityPrivate->checkRoleLevel("ROLE_ADMIN", $this->getUser()->getRoleId());
                    $checkRoles = $this->utilityPrivate->checkRoles($pageRow['role_id'], $this->getUser()->getRoleId());
                            
                    if ($chekRoleLevel == false && $checkRoles == false) {
                        // Page not available for role
                        $this->response['values']['controllerAction'] = null;
                        $this->response['values']['argument'] = $this->utility->getTranslator()->trans("pageLogicController_4");

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
                    $this->response['values']['argument'] = $this->utility->getTranslator()->trans("pageLogicController_5");
                    
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