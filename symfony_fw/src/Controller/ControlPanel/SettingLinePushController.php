<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;

use App\Entity\SettingLinePush;
use App\Form\SettingLinePushFormType;

class SettingLinePushController extends AbstractController {
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
    * @Route(
    *   name = "cp_setting_line_push_create",
    *   path = "/cp_setting_line_push_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting_line_push.html.twig")
    */
    public function createAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        // Logic
        $this->settingLinePushList();
        
        if ($request->get("event") == null) {
            if (isset($_SESSION['settingLinePushProfileId']) == false)
                $settingLinePushEntity = new SettingLinePush();
            else
                $settingLinePushEntity = $this->entityManager->getRepository("App\Entity\SettingLinePush")->find($_SESSION['settingLinePushProfileId']);
            
            $form = $this->createForm(SettingLinePushFormType::class, $settingLinePushEntity, Array(
                'validation_groups' => Array('setting_line_push')
            ));
            $form->handleRequest($request);
            
            if ($request->isMethod("POST") == true && $checkUserRole == true) {
                if ($form->isSubmitted() == true && $form->isValid() == true) {
                    if (isset($_SESSION['settingLinePushProfileId']) == true)
                        unset($_SESSION['settingLinePushProfileId']);
                    
                    // Database insert / update
                    $this->entityManager->persist($settingLinePushEntity);
                    $this->entityManager->flush();
                    
                    $this->settingLinePushList();
                    
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingLinePushController_1");
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingLinePushController_2");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
                
                return $this->ajax->response(Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));
            }
        }
        else if ($request->get("event") == "profile") {
            $id = $request->get("id") != null ? $request->get("id") : 0;
            
            $settingLinePushEntity = $this->entityManager->getRepository("App\Entity\SettingLinePush")->find($id);
            
            if ($settingLinePushEntity != null) {
                $_SESSION['settingLinePushProfileId'] = $id;
                
                $this->response['values']['entity'] = Array(
                    $settingLinePushEntity->getName(),
                    $settingLinePushEntity->getUserId(),
                    $settingLinePushEntity->getAccessToken()
                );
                
                $this->settingLinePushList();
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingLinePushController_3");
            
            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response
            ));
        }
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response,
            'form' => $form->createView()
        );
    }
    
    /**
    * @Route(
    *   name = "cp_setting_line_push_delete",
    *   path = "/cp_setting_line_push_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting_line_push.html.twig")
    */
    public function deleteAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") != null ? $request->get("id") : 0;
                    
                    $settingLinePushEntity = $this->entityManager->getRepository("App\Entity\SettingLinePush")->find($id);
                    
                    if ($settingLinePushEntity != null) {
                        if (isset($_SESSION['settingLinePushProfileId']) == true)
                            unset($_SESSION['settingLinePushProfileId']);
                        
                        // Database remove
                        $this->entityManager->remove($settingLinePushEntity);
                        $this->entityManager->flush();
                        
                        $this->settingLinePushList();

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingLinePushController_4");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingLinePushController_5");

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
            'response' => $this->response
        );
    }
    
    /**
    * @Route(
    *   name = "cp_setting_line_push_reset",
    *   path = "/cp_setting_line_push_reset/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting_line_push.html.twig")
    */
    public function resetAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "reset") {
                    unset($_SESSION['settingLinePushProfileId']);
                    
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingLinePushController_6");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingLinePushController_7");
                
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
            'response' => $this->response
        );
    }
    
    /**
    * @Route(
    *   name = "cp_setting_line_push_webhook",
    *   path = "/cp_setting_line_push_webhook",
    *	methods={"GET", "OPTIONS"}
    * )
    */
    public function webhookAction(Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        if ($request->isMethod("GET") == true) {
            //...
        }
        
        $response = new Response(json_encode($this->response));
        $response->headers->set("Access-Control-Allow-Origin", "*");
        $response->headers->set("Access-Control-Allow-Headers", "*");
        $response->headers->set("Access-Control-Allow-Methods", "GET, OPTIONS");
        $response->headers->set("Accept", "application/json");
        $response->headers->set("Content-Type", "application/json");
        
        return $response;
    }
    
    // Functions private
    private function settingLinePushList() {
        $rows = $this->query->selectAllSettingLinePushDatabase();
        
        $this->response['values']['wordTagListHtml'] = $this->utility->createWordTagListHtml($rows);
    }
}