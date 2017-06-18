<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Table;

use ReinventSoftware\UebusaitoBundle\Form\SearchFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\SearchModel;

class SearchController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $utility;
    private $query;
    private $ajax;
    private $table;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:module/search_words.html.twig")
     */
    public function wordsAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $this->utilityPrivate->checkSessionOverTime();
        
        if ($_SESSION['user_activity'] != "" ) {
            $this->response['session']['userActivity'] = $_SESSION['user_activity'];
            
            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response
            ));
        }
        
        // Create form
        $searchFormType = new SearchFormType();
        $form = $this->createForm($searchFormType, new SearchModel(), Array(
            'validation_groups' => Array(
                'search_words'
            )
        ));
        
        $moduleRow = $this->query->selectModuleDatabase(5);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST") {
            $form->handleRequest($this->utility->getRequestStack());

            // Check form
            if ($form->isValid() == true) {
                $words = $form->get("words")->getData();

                $this->response['values']['url'] = "{$this->utility->getUrlRoot()}{$this->utility->getWebsiteFile()}/{$this->urlLocale}/5/$words";
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("searchController_1");
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
     * @Template("UebusaitoBundle:render:search_result.html.twig")
     */
    public function resultAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->table = new Table($this->container, $this->entityManager);
        
        $this->response = Array();
        
        // Pagination
        $pageRows = $this->query->selectAllPagesDatabase($this->urlLocale, $this->urlExtra);
        
        $tableResult = $this->table->request($pageRows, 20, "searchResult", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml($tableResult['list']);;
        
        $this->response['values']['results'] = $pageRows;
        
        if ($this->table->checkPost() == true) {
            $render = $this->renderView("UebusaitoBundle::render/search_result.html.twig", Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response
            ));

            $this->response['render'] = $render;
            
            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response
            ));
        }
        else
            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("searchController_1");
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    // Functions private
    private function listHtml($tableResult) {
        $listHtml = "";
        
        foreach ($tableResult as $key => $value) {
            $listHtml .= "<div class=\"box\">
                <p class=\"title\"><b>{$value['title']}</b></p>";
                if (strlen($value['argument']) > 200)
                    $listHtml .= "<p class=\"argument\">" . substr($value['argument'], 0, 200) . "...</p>";
                else
                    $listHtml .= "<p class=\"argument\">{$value['argument']}</p>
                <a href=\"{$this->utility->getUrlRoot()}{$this->utility->getWebsiteFile()}/{$this->utility->getRequestStack()->get("_locale")}/{$value['id']}\">{$this->utility->getTranslator()->trans("searchController_2")}</a>
            </div>";
        }
        
        return $listHtml;
    }
}