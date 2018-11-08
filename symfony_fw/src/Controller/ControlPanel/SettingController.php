<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;

use App\Form\SettingFormType;

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
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting.html.twig")
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
        $settingEntity = $this->entityManager->getRepository("App\Entity\Setting")->find(1);
        
        $languageCustomData = $this->languageCustomData($this->query);
        
        $form = $this->createForm(SettingFormType::class, $settingEntity, Array(
            'validation_groups' => Array('setting'),
            'choicesTemplate' => $this->utility->createTemplateList(),
            'choicesLanguage' => array_column($languageCustomData, "value", "text")
        ));
        $form->handleRequest($request);
        
        $this->response['values']['userRoleSelectHtml'] = $this->utility->createUserRoleSelectHtml("form_setting_roleUserId_select", "settingController_1", true);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                if ($form->get("templateColumn")->getData() != $settingEntity->getTemplateColumn())
                    $this->moduleDatabase($form->get("templateColumn")->getData(), $this->query);
                
                // Database update
                $this->entityManager->persist($settingEntity);
                $this->entityManager->flush();
                    
                if ($form->get("https")->getData() != $settingEntity->getHttps()) {
                    $this->utility->getTokenStorage()->setToken(null);
                    
                    $message = $this->utility->getTranslator()->trans("settingController_2");
                    
                    $_SESSION['userActivity'] = $message;
                    
                    $this->response['messages']['info'] = $message;
                }
                else
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_3");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingController_4");
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
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting.html.twig")
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
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "deleteLanguage") {
                    $code = $request->get("code");

                    $settingDatabase = $this->settingDatabase("deleteLanguage", $code);

                    if ($settingDatabase == true) {
                        unlink("{$this->utility->getPathRoot()}/translations/messages.$code.yml");

                        $this->settingDatabase("deleteLanguageInPage", $code);

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_5");
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
                                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_6");
                            else if ($request->get("event") == "createLanguage") {
                                touch("{$this->utility->getPathRoot()}/translations/messages.$code.yml");

                                $this->settingDatabase("insertLanguageInPage", $code);

                                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_7");
                            }
                        }
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingController_8");
                }

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
    
    // Functions private
    private function languageCustomData($query) {
        $languageRows = $query->selectAllLanguageDatabase();
        
        $customData = Array();
        
        foreach($languageRows as $key => $value) {
            $customData[$key]['value'] = $value['code'];
            $customData[$key]['text'] = "{$value['code']} | {$value['date']}";
        }
        
        return $customData;
    }
    
    private function moduleDatabase($templateColumn, $query) {
        if ($templateColumn == 1) {
            $queryModule = $this->utility->getConnection()->prepare("UPDATE modules
                                                                        SET position_tmp = :positionTmp,
                                                                            position = :position
                                                                        WHERE id != :id
                                                                        AND position_tmp = :position");
            
            $queryModule->bindValue(":id", 3);
            $queryModule->bindValue(":positionTmp", "");
            $queryModule->bindValue(":position", "right");
            
            $queryModule->execute();
            
            $queryModule->bindValue(":positionTmp", "");
            $queryModule->bindValue(":position", "left");
            
            $queryModule->execute();
        }
        else if ($templateColumn == 2 || $templateColumn == 3 || $templateColumn == 4) {
            $queryModule = $this->utility->getConnection()->prepare("UPDATE modules
                                                                        SET position_tmp = :positionTmp,
                                                                            position = :position
                                                                        WHERE position = :positionTmp");
            
            if ($templateColumn == 2) {
                $queryModule->bindValue(":positionTmp", "right");
                $queryModule->bindValue(":position", "center");
                
                $queryModule->execute();
                
                $queryModule = $this->utility->getConnection()->prepare("UPDATE modules
                                                                            SET position_tmp = :positionTmp,
                                                                                position = :position
                                                                            WHERE position_tmp = :position");

                $queryModule->bindValue(":positionTmp", "");
                $queryModule->bindValue(":position", "left");
            }
            else if ($templateColumn == 3) {
                $queryModule->bindValue(":positionTmp", "left");
                $queryModule->bindValue(":position", "center");
                
                $queryModule->execute();
                
                $queryModule = $this->utility->getConnection()->prepare("UPDATE modules
                                                                            SET position_tmp = :positionTmp,
                                                                                position = :position
                                                                            WHERE position_tmp = :position");

                $queryModule->bindValue(":positionTmp", "");
                $queryModule->bindValue(":position", "right");
            }
            else if ($templateColumn == 4) {
                $queryModule->bindValue(":positionTmp", "right");
                $queryModule->bindValue(":position", "center");

                $queryModule->execute();

                $queryModule->bindValue(":positionTmp", "left");
                $queryModule->bindValue(":position", "center");
            }
            
            $queryModule->execute();
        }
        
        $this->updateModuleRankInColumn("left", $query);
        $this->updateModuleRankInColumn("center", $query);
        $this->updateModuleRankInColumn("right", $query);
    }
    
    private function updateModuleRankInColumn($position, $query) {
        $moduleRows = $query->selectAllModuleDatabase(null, $position);
        
        foreach($moduleRows as $key => $value) {
            $queryModule = $this->utility->getConnection()->prepare("UPDATE modules
                                                                        SET rank_in_column = :rankInColumn
                                                                        WHERE id = :id");
            
            $queryModule->bindValue(":rankInColumn", $key + 1);
            $queryModule->bindValue(":id", $value['id']);
            
            $queryModule->execute();
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