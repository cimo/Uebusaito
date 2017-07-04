<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

use ReinventSoftware\UebusaitoBundle\Form\SettingsFormType;

class SettingController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $utilityPrivate;
    private $query;
    private $ajax;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_settings_modify",
    *   path = "/cp_settings_modify/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/settings.html.twig")
    */
    public function modifyAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        $this->response['values']['rolesSelect'] = $this->utilityPrivate->createRolesSelectHtml("form_settings_roleId_field", true);
        
        $settingEntity = $this->entityManager->getRepository("UebusaitoBundle:Setting")->find(1);
        
        $settingRow = $this->query->selectSettingDatabase();
        
        // Form
        $form = $this->createForm(SettingsFormType::class, $settingEntity, Array(
            'validation_groups' => Array('settings'),
            'settingRow' => $settingRow,
            'choicesTemplate' => $this->utilityPrivate->createTemplatesList(),
            'choicesLanguage' => array_column($this->query->selectAllLanguagesDatabase(), "code", "code")
        ));
        $form->handleRequest($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($form->isValid() == true) {
                // Update in database
                $this->entityManager->persist($settingEntity);
                $this->entityManager->flush();

                if ($form->get("https")->getData() != $settingRow['https'])
                    $this->response['action']['refresh'] = true;
                else
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_1");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingController_2");
                $this->response['errors'] = $this->ajax->errors($form);
            }
            
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
    *   name = "cp_settings_language_manage",
    *   path = "/cp_settings_language_manage/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/settings.html.twig")
    */
    public function languageManageAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($request->get("event") == "deleteLanguage" && $this->utilityPrivate->checkToken($request) == true) {
                $code = $request->get("code");

                $settingsDatabase = $this->settingsDatabase("deleteLanguage", $code);

                if ($settingsDatabase == true) {
                    unlink("{$this->utility->getPathSrcBundle()}/Resources/translations/messages.$code.yml");

                    $this->settingsDatabase("deleteLanguageInPage", $code);

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_3");
                }
            }
            else if ($request->get("event") == "createLanguage" && $this->utilityPrivate->checkToken($request) == true) {
                $code = $request->get("code");

                $languageRows = $this->query->selectAllLanguagesDatabase();

                $exists = false;

                foreach ($languageRows as $key => $value) {
                    if ($code == $value['code']) {
                        $exists = true;

                        break;
                    }
                }

                if ($code != "" && $exists == false) {
                    $settingsDatabase = $this->settingsDatabase("insertLanguage", $code);

                    if ($settingsDatabase == true) {
                        touch("{$this->utility->getPathSrcBundle()}/Resources/translations/messages.$code.yml");

                        $this->settingsDatabase("insertLanguageInPage", $code);

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_4");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingController_5");
            }
            
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
            'response' => $this->response
        );
    }
    
    // Functions private
    private function settingsDatabase($type, $code) {
        if ($type == "deleteLanguage") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM languages
                                                                WHERE id > :idExclude
                                                                AND code = :code");

            $query->bindValue(":idExclude", 2);
            $query->bindValue(":code", $code);

            return $query->execute();
        }
        else if ($type == "deleteLanguageInPage") {
            $codeTmp = is_string($code) == true ? $code : "";
            $codeTmp = strlen($codeTmp) == true ? $codeTmp : "";
            $codeTmp = ctype_alpha($codeTmp) == true ? $codeTmp : "";
            
            $query = $this->utility->getConnection()->prepare("ALTER TABLE pages_titles DROP $codeTmp;
                                                                ALTER TABLE pages_arguments DROP $codeTmp;
                                                                ALTER TABLE pages_menu_names DROP $codeTmp;");
            
            $query->execute();
        }
        else if ($type == "insertLanguage") {
            $query = $this->utility->getConnection()->prepare("INSERT INTO languages (code)
                                                                VALUES (:code)");
            
            $query->bindValue(":code", $code);
            
            return $query->execute();
        }
        else if ($type == "insertLanguageInPage") {
            $codeTmp = is_string($code) == true ? $code : "";
            $codeTmp = strlen($codeTmp) == true ? $codeTmp : "";
            $codeTmp = ctype_alpha($codeTmp) == true ? $codeTmp : "";
            
            $query = $this->utility->getConnection()->prepare("ALTER TABLE pages_titles ADD $codeTmp VARCHAR(255);
                                                                ALTER TABLE pages_arguments ADD $codeTmp LONGTEXT;
                                                                ALTER TABLE pages_menu_names ADD $codeTmp VARCHAR(255);");
            
            $query->execute();
        }
    }
}