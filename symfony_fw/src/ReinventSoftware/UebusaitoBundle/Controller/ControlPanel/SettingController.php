<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

use ReinventSoftware\UebusaitoBundle\Form\SettingFormType;

class SettingController extends Controller {
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
    *   name = "cp_setting_save",
    *   path = "/cp_setting_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/setting.html.twig")
    */
    public function saveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser()->getRoleUserId());
        
        // Logic
        $settingEntity = $this->entityManager->getRepository("UebusaitoBundle:Setting")->find(1);
        
        $languageCustomData = $this->languageCustomData();
        
        $form = $this->createForm(SettingFormType::class, $settingEntity, Array(
            'validation_groups' => Array('setting'),
            'choicesTemplate' => $this->utility->createTemplateList(),
            'choicesLanguage' => array_column($languageCustomData, "value", "text")
        ));
        $form->handleRequest($request);
        
        $this->response['values']['roleUserHtml'] = $this->utility->createUserRoleHtml("form_setting_roleUserId_field", true);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isValid() == true) {
                if ($form->get("templateColumn")->getData() != $settingEntity->getTemplateColumn())
                    $this->moduleDatabase($form->get("templateColumn")->getData());
                
                // Update in database
                $this->entityManager->persist($settingEntity);
                $this->entityManager->flush();
                    
                if ($form->get("https")->getData() != $settingEntity->getHttps()) {
                    $this->utility->getTokenStorage()->setToken(null);
                    
                    $message = $this->utility->getTranslator()->trans("settingController_1");
                    
                    $_SESSION['user_activity'] = $message;
                    
                    $this->response['messages']['info'] = $message;
                }
                else
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_2");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingController_3");
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
    *   name = "cp_setting_language_manage",
    *   path = "/cp_setting_language_manage/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/setting.html.twig")
    */
    public function languageManageAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true && $this->utility->checkToken($request) == true) {
            if ($request->get("event") == "deleteLanguage") {
                $code = $request->get("code");

                $settingDatabase = $this->settingDatabase("deleteLanguage", $code);

                if ($settingDatabase == true) {
                    unlink("{$this->utility->getPathSrcBundle()}/Resources/translations/messages.$code.yml");

                    $this->settingDatabase("deleteLanguageInPage", $code);

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_4");
                }
            }
            else if ($request->get("event") == "modifyLanguage" || $request->get("event") == "createLanguage") {
                $code = $request->get("code");
                $date = $request->get("date");
                
                $languageRows = $this->query->selectAllLanguageDatabase();

                $exists = false;
                $checked = false;
                
                if ($request->get("event") == "createLanguage") {
                    foreach ($languageRows as $key => $value) {
                        if ($code == $value['code']) {
                            $exists = true;

                            break;
                        }
                    }
                }
                
                if (strtolower($date) == "y-m-d" || strtolower($date) == "d-m-y" || strtolower($date) == "m-d-y")
                    $checked = true;

                if ($code != "" && $date != "" && $exists == false && $checked == true) {
                    $settingDatabase = false;
                    
                    if ($request->get("event") == "modifyLanguage")
                        $settingDatabase = $this->settingDatabase("updateLanguage", $code, $date);
                    else if ($request->get("event") == "createLanguage")
                        $settingDatabase = $this->settingDatabase("insertLanguage", $code, $date);
                    
                    if ($settingDatabase == true) {
                        if ($request->get("event") == "modifyLanguage")
                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_5");
                        else if ($request->get("event") == "createLanguage") {
                            touch("{$this->utility->getPathSrcBundle()}/Resources/translations/messages.$code.yml");

                            $this->settingDatabase("insertLanguageInPage", $code);
                            
                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_6");
                        }
                    }
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingController_7");
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
    private function languageCustomData() {
        $languageRows = $this->query->selectAllLanguageDatabase();
        
        $customData = Array();
        
        foreach($languageRows as $key => $value) {
            $customData[$key]['value'] = $value['code'];
            $customData[$key]['text'] = "{$value['code']} | {$value['date']}";
        }
        
        return $customData;
    }
    
    private function moduleDatabase($templateColumn) {
        if ($templateColumn == 1) {
            $query = $this->utility->getConnection()->prepare("UPDATE modules
                                                                SET position_tmp = :positionTmp,
                                                                    position = :position
                                                                WHERE id != :id
                                                                AND position_tmp = :position");
            
            $query->bindValue(":id", 3);
            $query->bindValue(":positionTmp", "");
            $query->bindValue(":position", "right");
            
            $query->execute();
            
            $query->bindValue(":positionTmp", "");
            $query->bindValue(":position", "left");
            
            $query->execute();
        }
        else if ($templateColumn == 2 || $templateColumn == 3 || $templateColumn == 4) {
            $query = $this->utility->getConnection()->prepare("UPDATE modules
                                                                SET position_tmp = :positionTmp,
                                                                    position = :position
                                                                WHERE position = :positionTmp");
            
            if ($templateColumn == 2) {
                $query->bindValue(":positionTmp", "right");
                $query->bindValue(":position", "center");
                
                $query->execute();
                
                $query = $this->utility->getConnection()->prepare("UPDATE modules
                                                                    SET position_tmp = :positionTmp,
                                                                        position = :position
                                                                    WHERE position_tmp = :position");

                $query->bindValue(":positionTmp", "");
                $query->bindValue(":position", "left");
            }
            else if ($templateColumn == 3) {
                $query->bindValue(":positionTmp", "left");
                $query->bindValue(":position", "center");
                
                $query->execute();
                
                $query = $this->utility->getConnection()->prepare("UPDATE modules
                                                                    SET position_tmp = :positionTmp,
                                                                        position = :position
                                                                    WHERE position_tmp = :position");

                $query->bindValue(":positionTmp", "");
                $query->bindValue(":position", "right");
            }
            else if ($templateColumn == 4) {
                $query->bindValue(":positionTmp", "right");
                $query->bindValue(":position", "center");

                $query->execute();

                $query->bindValue(":positionTmp", "left");
                $query->bindValue(":position", "center");
            }
            
            $query->execute();
        }
        
        $this->updateModulePositionInColumn("left");
        $this->updateModulePositionInColumn("center");
        $this->updateModulePositionInColumn("right");
    }
    
    private function updateModulePositionInColumn($position) {
        $moduleRows = $this->query->selectAllModuleDatabase(null, $position);
        
        foreach($moduleRows as $key => $value) {
            $query = $this->utility->getConnection()->prepare("UPDATE modules
                                                                SET position_in_column = :positionInColumn
                                                                WHERE id = :id");
            
            $query->bindValue(":sort", $key + 1);
            $query->bindValue(":id", $value['id']);
            
            $query->execute();
        }
    }
    
    private function settingDatabase($type, $code, $date = null) {
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
        else if ($type == "updateLanguage") {
            $query = $this->utility->getConnection()->prepare("UPDATE languages
                                                                SET date = :date
                                                                WHERE code = :code");
            
            $query->bindValue(":date", $date);
            $query->bindValue(":code", $code);
            
            return $query->execute();
        }
        else if ($type == "insertLanguage") {
            $query = $this->utility->getConnection()->prepare("INSERT INTO languages (code, date)
                                                                VALUES (:code, :date)");
            
            $query->bindValue(":code", $code);
            $query->bindValue(":date", $date);
            
            return $query->execute();
        }
        else if ($type == "insertLanguageInPage") {
            $codeTmp = is_string($code) == true ? $code : "";
            $codeTmp = strlen($codeTmp) == true ? $codeTmp : "";
            $codeTmp = ctype_alpha($codeTmp) == true ? $codeTmp : "";
            
            $query = $this->utility->getConnection()->prepare("ALTER TABLE pages_titles ADD $codeTmp VARCHAR(255) DEFAULT NULL;
                                                                ALTER TABLE pages_arguments ADD $codeTmp LONGTEXT DEFAULT NULL;
                                                                ALTER TABLE pages_menu_names ADD $codeTmp VARCHAR(255) DEFAULT '-';");
            
            $query->execute();
        }
    }
}