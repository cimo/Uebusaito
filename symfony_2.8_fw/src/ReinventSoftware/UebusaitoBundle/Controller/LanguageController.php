<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

use ReinventSoftware\UebusaitoBundle\Form\LanguageFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\LanguageModel;

class LanguageController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $utility;
    private $query;
    private $ajax;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:module/language_text.html.twig")
     */
    public function textAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        // Create form
        $languageFormType = new LanguageFormType($this->container, $this->entityManager, $this->urlLocale, "text");
        $form = $this->createForm($languageFormType, new LanguageModel(), Array(
            'validation_groups' => Array(
                'language_text'
            )
        ));
        
        $moduleRow = $this->query->selectModuleFromDatabase(4);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->utility->getRequestStack());
                
                // Check form
                if ($form->isValid() == true)
                    $this->response['values']['url'] = $this->utility->getUrlRoot() . "/" . $form->get("codeText")->getData() . "/" . $this->utility->getRequestStack()->attributes->get("urlCurrentPageId") . "/" . $this->utility->getRequestStack()->attributes->get("urlExtra");
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("languageController_1");
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
     * @Template("UebusaitoBundle:render:language_page.html.twig")
     */
    public function pageAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        // Create form
        $languageFormType = new LanguageFormType($this->container, $this->entityManager, $this->urlLocale, "page");
        $form = $this->createForm($languageFormType, new LanguageModel(), Array(
            'validation_groups' => Array(
                'language_code'
            )
        ));
        
        $this->response['values']['languages'] = $this->query->selectAllLanguagesFromDatabase();
        $this->response['settings'] = $this->utility->getSettings();
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->utility->getRequestStack());

                // Check form
                if ($form->isValid() == true) {
                    $codePage = $form->get("codePage")->getData();

                    $this->response['values']['codePage'] = $codePage;
                    $this->response['values']['pageFields'] = $this->query->selectPageFromDatabase($codePage, $this->urlExtra);
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("languageController_2");
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
    
    // Functions private
}