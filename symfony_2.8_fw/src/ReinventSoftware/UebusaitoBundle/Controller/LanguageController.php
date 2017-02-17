<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Form\LanguageFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\LanguageModel;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

class LanguageController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    private $requestStack;
    private $translator;
    
    private $utility;
    private $ajax;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:language_text.html.twig")
     */
    public function textAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        // Create form
        $languageFormType = new LanguageFormType($this->urlLocale, $this->utility, "text");
        $form = $this->createForm($languageFormType, new LanguageModel(), Array(
            'validation_groups' => Array(
                'language_text'
            )
        ));
        
        $moduleRow = $this->utility->getQuery()->selectModuleFromDatabase(4);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);
                
                // Check form
                if ($form->isValid() == true) {
                    $codeText = $form->get("codeText")->getData();
                    
                    $this->response['values']['url'] = $this->utility->getUrlRoot() . "/" . $codeText . "/" . $this->requestStack->attributes->get("urlCurrentPageId") . "/" . $this->requestStack->attributes->get("urlExtra");
                }
                else
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
     * @Template("UebusaitoBundle:render:language_page.html.twig")
     */
    public function pageAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        // Create form
        $languageFormType = new LanguageFormType($this->urlLocale, $this->utility, "page");
        $form = $this->createForm($languageFormType, new LanguageModel(), Array(
            'validation_groups' => Array(
                'language_code'
            )
        ));
        
        $this->response['values']['languages'] = $this->utility->getQuery()->selectAllLanguagesFromDatabase();
        $this->response['settings'] = $this->utility->getSettings();
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);

                // Check form
                if ($form->isValid() == true) {
                    $codePage = $form->get("codePage")->getData();

                    $this->response['values']['codePage'] = $codePage;
                    $this->response['values']['pageFields'] = $this->utility->getQuery()->selectPageFromDatabase($codePage, $this->urlExtra);
                }
                else
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