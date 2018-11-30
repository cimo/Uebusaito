<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;

class PageViewController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    
    // Properties
    
    // Functions public
    /**
    * @Template("@templateRoot/render/module/page_view.html.twig")
    */
    public function moduleAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = ($urlCurrentPageId <= 0 || empty($urlCurrentPageId) == true) ? 2 : $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        // Logic
        $moduleEntity = $this->entityManager->getRepository("App\Entity\Module")->find(2);
        
        $this->response['module']['id'] = $moduleEntity->getId();
        $this->response['module']['label'] = $moduleEntity->getLabel();
        
        $this->response['values']['controllerAction'] = null;
        $this->response['values']['title'] = $this->utility->getTranslator()->trans("pageViewController_1");
        $this->response['values']['argument'] = $this->utility->getTranslator()->trans("pageViewController_2");
        $this->response['values']['comment'] = false;
        $this->response['values']['userCreate'] = "-";
        $this->response['values']['dateCreate'] = "-";
        $this->response['values']['userModify'] = "-";
        $this->response['values']['dateModify'] = "-";
        
        $pageRow = $this->query->selectPageDatabase($this->urlLocale, $this->urlCurrentPageId);
        
        if ($pageRow != false) {
            $this->response['values']['controllerAction'] = $pageRow['controller_action'];
            $this->response['values']['title'] = $pageRow['title'];
            $this->response['values']['argument'] = html_entity_decode($pageRow['argument'], ENT_QUOTES, "utf-8");
            $this->response['values']['comment'] = $pageRow['comment'];
            $this->response['values']['userCreate'] = $pageRow['user_create'];
            $this->response['values']['dateCreate'] = strpos($pageRow['date_create'], "0000") !== false ? "-" : $this->utility->dateFormat($pageRow['date_create']);
            $this->response['values']['userModify'] = $pageRow['user_modify'];
            $this->response['values']['dateModify'] = strpos($pageRow['date_modify'], "0000") !== false ? "-" : $this->utility->dateFormat($pageRow['date_modify']);
            
            if ($this->utility->getAuthorizationChecker()->isGranted("IS_AUTHENTICATED_FULLY") == true) {
                if ($pageRow['protected'] == false && ($pageRow['id'] == 3 || $pageRow['id'] == 4)) {
                    // Page not available with login
                    $this->response['values']['controllerAction'] = null;
                    $this->response['values']['argument'] = $this->utility->getTranslator()->trans("pageViewController_3");

                    return Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $pageRow['id'],
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    );
                }
                else if ($pageRow['protected'] == true) {
                    $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
                    
                    if ($checkUserRole == true) {
                        $arrayExplodeFindValue = $this->utility->arrayExplodeFindValue($pageRow['role_user_id'], $this->getUser()->getRoleUserId());

                        if ($arrayExplodeFindValue == false) {
                            // Page not available for role
                            $this->response['values']['controllerAction'] = null;
                            $this->response['values']['argument'] = $this->utility->getTranslator()->trans("pageViewController_4");

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
                    $this->response['values']['argument'] = $this->utility->getTranslator()->trans("pageViewController_5");
                    
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