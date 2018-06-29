<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\System\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\System\TableAndPagination;

use ReinventSoftware\UebusaitoBundle\Form\SearchFormType;

class SearchController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    private $tableAndPagination;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "search_module",
    *   path = "/search_module/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/module/widget_search.html.twig")
    */
    public function moduleAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        // Logic
        $form = $this->createForm(SearchFormType::class, null, Array(
            'validation_groups' => Array('search_module')
        ));
        $form->handleRequest($request);
        
        $moduleRow = $this->query->selectModuleDatabase(5);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        if ($request->isMethod("POST") == true) {
            if ($form->isValid() == true || $this->isCsrfTokenValid("intention", $request->get("form_search")['_token']) == true) {
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
    * @Route(
    *   name = "search_render",
    *   path = "/search_render/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/search.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->tableAndPagination = new TableAndPagination($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        // Logic
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale, $this->urlExtra);
        
        if ($this->urlExtra == "")
            $pageRows = Array();
        
        $tableAndPagination = $this->tableAndPagination->request($pageRows, 20, "searchRender", true, true);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = count($tableAndPagination['listHtml']);
        
        if ($this->tableAndPagination->checkPost() == true) {
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
    private function createListHtml($tableResult) {
        if (count($tableResult) == 0)
            return "";
        
        $listHtml = "<ul class=\"mdc-list mdc-list--two-line mdc-list--avatar-list\">";
        
        foreach ($tableResult as $key => $value) {      
            $listHtml .= "<li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\" aria-hidden=\"true\">receipt</span>
                <span class=\"mdc-list-item__text\">
                    {$value['title']}
                    <span class=\"mdc-list-item__secondary-text\">";
                        $argument = preg_replace("/<(.*?)>/", " ", html_entity_decode($value['argument'], ENT_QUOTES, "utf-8"));
                        
                        if (strlen($argument) > 200)
                            $listHtml .= substr($argument, 0, 200) . "...";
                        else
                            $listHtml .= $argument;
                    $listHtml .= "</span>
                </span>
                <a class=\"mdc-list-item__meta material-icons\" href=\"{$this->utility->getUrlRoot()}{$this->utility->getWebsiteFile()}/{$this->urlLocale}/{$value['id']}\" aria-hidden=\"true\">
                    info
                </a>
            </li>";
            
            if ($key < (count($tableResult) - 1))
                $listHtml .= "<li role=\"separator\" class=\"mdc-list-divider\"></li>";
        }
        
        $listHtml .= "</ul>";
        
        return $listHtml;
    }
}