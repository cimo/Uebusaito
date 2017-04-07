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
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST") {
            $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->utility->getRequestStack());
                
                // Check form
                if ($form->isValid() == true) {
                    $https = $form->get("https")->getData();
                    
                    if ($https != $this->utility->getSettings()['https'])
                        $this->response['action']['refresh'] = true;
                    else
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_1");
                    
                    // Insert in database
                    $this->entityManager->persist($settingEntity);
                    $this->entityManager->flush();
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
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST") {
            $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                if (isset($_SESSION['token']) == true && $this->utility->getRequestStack()->request->get("token") == $_SESSION['token'] && $this->utility->getRequestStack()->request->get("event") == "delete") {
                    $currentIndex = $this->utility->getRequestStack()->request->get("currentIndex");
                    
                    if ($currentIndex > 2) {
                        $languageRows = $this->query->selectLanguageFromDatabase($currentIndex);
                        
                        $this->settingsInDatabase("delete", $currentIndex);
                        
                        unlink("{$this->utility->getPathBundle()}/Resources/translations/messages.{$languageRows['code']}.yml");
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_3");
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingController_4");
                }
                else if (isset($_SESSION['token']) == true && $this->utility->getRequestStack()->request->get("token") == $_SESSION['token'] && $this->utility->getRequestStack()->request->get("event") == "create") {
                    $code = $this->utility->getRequestStack()->request->get("code");
                    
                    $languageRows = $this->query->selectAllLanguagesFromDatabase();
                    
                    $exists = false;
                    
                    foreach ($languageRows as $key => $value) {
                        if ($code == $value['code']) {
                            $exists = true;

                            break;
                        }
                    }
                    
                    if ($code != "" && $exists == false) {
                        $this->settingsInDatabase("create", $code);
                        
                        touch("{$this->utility->getPathBundle()}/Resources/translations/messages.$code.yml");
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingController_5");
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingController_6");
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
    private function settingsInDatabase($type, $value) {
        if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM languages
                                                                WHERE id > :idExclude
                                                                AND id = :id");

            $query->bindValue(":idExclude", 2);
            $query->bindValue(":id", $value);

            $query->execute();
        }
        else if ($type == "create") {
            $query = $this->utility->getConnection()->prepare("INSERT INTO languages (code)
                                                                VALUES (:code);");
            
            $query->bindValue(":code", $value);
            
            $query->execute();
        }
    }
}