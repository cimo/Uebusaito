<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Form\SearchFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\SearchModel;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

class SearchController extends Controller {
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
     * @Template("UebusaitoBundle:render:search.html.twig")
     */
    public function indexAction($_locale, $urlCurrentPageId, $urlExtra) {
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
        $searchFormType = new SearchFormType();
        $form = $this->createForm($searchFormType, new SearchModel(), Array(
            'validation_groups' => Array(
                'search'
            )
        ));
        
        $moduleRow = $this->utility->getQuery()->selectModuleFromDatabase(5);
        
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