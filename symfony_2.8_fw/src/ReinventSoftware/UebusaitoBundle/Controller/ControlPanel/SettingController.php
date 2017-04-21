<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
    
    private $utility;
    private $utilityPrivate;
    private $query;
    private $ajax;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:control_panel/settings.html.twig")
     */
    public function modifyAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $settingEntity = $this->entityManager->getRepository("UebusaitoBundle:Setting")->find(1);
        
        // Create form
        $settingsFormType = new SettingsFormType($this->container, $this->entityManager, $this->urlLocale);
        $form = $this->createForm($settingsFormType, $settingEntity, Array(
            'validation_groups' => Array(
                'settings'
            )
        ));
        
        $this->response['values']['rolesSelect'] = $this->utilityPrivate->createRolesSelectHtml("form_settings_roleId_field", true);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->utility->getRequestStack());
                
                // Check form
                if ($form->isValid() == true) {
                    // Update in database
                    $this->entityManager->persist($settingEntity);
                    $this->entityManager->flush();
                    
                    if ($form->get("https")->getData() != $this->utility->getSettings()['https'])
                        $this->response['action']['refresh'] = true;
                    else
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_1");
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingController_2");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
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
     * @Template("UebusaitoBundle:render:control_panel/settings.html.twig")
     */
    public function languageManageAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                if ($this->utility->getRequestStack()->request->get("event") == "deleteLanguage" && $this->utilityPrivate->checkToken() == true) {
                    $currentIndex = $this->utility->getRequestStack()->request->get("currentIndex");
                    
                    $languageRows = $this->query->selectLanguageDatabase($currentIndex);

                    $settingsDatabase = $this->settingsDatabase("deleteLanguage", $currentIndex);
                    
                    if ($settingsDatabase == true) {
                        unlink("{$this->utility->getPathBundleFull()}/Resources/translations/messages.{$languageRows['code']}.yml");
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_3");
                    }
                }
                else if ($this->utility->getRequestStack()->request->get("event") == "createLanguage" && $this->utilityPrivate->checkToken() == true) {
                    $code = $this->utility->getRequestStack()->request->get("code");
                    
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
                            touch("{$this->utility->getPathBundleFull()}/Resources/translations/messages.$code.yml");
                            
                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_4");
                        }
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingController_5");
                }
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
    private function settingsDatabase($type, $value) {
        if ($type == "deleteLanguage") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM languages
                                                                WHERE id > :idExclude
                                                                AND id = :id");

            $query->bindValue(":idExclude", 2);
            $query->bindValue(":id", $value);

            return $query->execute();
        }
        else if ($type == "insertLanguage") {
            $query = $this->utility->getConnection()->prepare("INSERT INTO languages (code)
                                                                VALUES (:code);");
            
            $query->bindValue(":code", $value);
            
            return $query->execute();
        }
    }
}