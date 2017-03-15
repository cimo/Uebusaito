<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Form\SettingsFormType;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

class SettingController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    private $requestStack;
    private $translator;
    
    private $utility;
    private $ajax;
    
    private $settings;
    
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->settings = $this->utility->getSettings();
        
        $this->response = Array();
        
        $setting = $this->entityManager->getRepository("UebusaitoBundle:Setting")->find(1);
        
        // Create form
        $settingsFormType = new SettingsFormType($this->urlLocale, $this->utility);
        $form = $this->createForm($settingsFormType, $setting, Array(
            'validation_groups' => Array(
                'settings'
            )
        ));
        
        $this->response['values']['rolesSelect'] = $this->utility->createRolesSelectHtml("form_settings_roleId_field", true);
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);
                
                // Check form
                if ($form->isValid() == true) {
                    $https = $form->get("https")->getData();
                    
                    if ($https != $this->settings['https'])
                        $this->response['action']['refresh'] = true;
                    else
                        $this->response['messages']['success'] = $this->translator->trans("settingController_1");
                    
                    // Insert in database
                    $this->entityManager->persist($setting);
                    $this->entityManager->flush();
                }
                else {
                    $this->response['messages']['error'] = $this->translator->trans("settingController_2");
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                if (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $this->requestStack->request->get("event") == "delete") {
                    $currentIndex = $this->requestStack->request->get("currentIndex");
                    
                    if ($currentIndex > 2) {
                        $languageRows = $this->utility->getQuery()->selectLanguageFromDatabase($currentIndex);
                        
                        $this->settingsInDatabase("delete", $currentIndex);
                        
                        unlink("{$this->utility->getPathBundle()}/Resources/translations/messages.{$languageRows['code']}.yml");
                        
                        $this->response['messages']['success'] = $this->translator->trans("settingController_3");
                    }
                    else
                        $this->response['messages']['error'] = $this->translator->trans("settingController_4");
                }
                else if (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $this->requestStack->request->get("event") == "create") {
                    $code = $this->requestStack->request->get("code");
                    
                    $languageRows = $this->utility->getQuery()->selectAllLanguagesFromDatabase();
                    
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
                        
                        $this->response['messages']['success'] = $this->translator->trans("settingController_5");
                    }
                    else
                        $this->response['messages']['error'] = $this->translator->trans("settingController_6");
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
        $connection = $this->entityManager->getConnection();
        
        if ($type == "delete") {
            $query = $connection->prepare("DELETE FROM languages
                                            WHERE id > :idExclude
                                            AND id = :id");

            $query->bindValue(":idExclude", 2);
            $query->bindValue(":id", $value);

            $query->execute();
        }
        else if ($type == "create") {
            $query = $connection->prepare("INSERT INTO languages (code)
                                            VALUES (:code);");
            
            $query->bindValue(":code", $value);
            
            $query->execute();
        }
    }
}