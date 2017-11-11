<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UebusaitoUtility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

use ReinventSoftware\UebusaitoBundle\Form\LanguageFormType;

class LanguageController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $uebusaitoUtility;
    private $query;
    private $ajax;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "language_text",
    *   path = "/language_text/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/module/language_text.html.twig")
    */
    public function textAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        // Logic
        $moduleRow = $this->query->selectModuleDatabase(4);
        $languageRow = $this->query->selectLanguageDatabase($this->urlLocale);
        
        $form = $this->createForm(LanguageFormType::class, null, Array(
            'validation_groups' => Array('language_text'),
            'type' => "text",
            'choicesCodeText' => array_column($this->query->selectAllLanguageDatabase(), "code", "code"),
            'preferredChoicesCodeText' => $languageRow['code']
        ));
        $form->handleRequest($request);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        if ($request->isMethod("POST") == true) {
            if ($form->isValid() == true)
                $this->response['values']['url'] = "{$this->utility->getUrlRoot()}{$this->utility->getWebsiteFile()}/{$form->get("codeText")->getData()}/{$request->get("urlCurrentPageId")}/{$request->get("urlExtra")}";
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("languageController_1");
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
    *   name = "language_page",
    *   path = "/language_page/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/module/language_page.html.twig")
    */
    public function pageAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->utility->checkSessionOverTime($request);
        
        // Logic
        $form = $this->createForm(LanguageFormType::class, null, Array(
            'validation_groups' => Array('language_code'),
            'type' => "page",
            'choicesCodeText' => null,
            'preferredChoicesCodeText' => null
        ));
        $form->handleRequest($request);
        
        $this->response['values']['languageRows'] = $this->query->selectAllLanguageDatabase();
        $this->response['values']['settingRow'] = $this->query->selectSettingDatabase();
        
        if ($request->isMethod("POST") == true) {
            if ($form->isValid() == true) {
                $codePage = $form->get("codePage")->getData();
                $pageRow = $this->query->selectPageDatabase($codePage, $this->urlExtra);
                
                $this->response['values']['codePage'] = $codePage;
                $this->response['values']['pageTitle'] = $pageRow['title'];
                $this->response['values']['pageArgument'] = html_entity_decode($pageRow['argument'], ENT_QUOTES, "utf-8");
                $this->response['values']['pageMenuName'] = $pageRow['menu_name'];
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("languageController_2");
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
    
    // Functions private
}