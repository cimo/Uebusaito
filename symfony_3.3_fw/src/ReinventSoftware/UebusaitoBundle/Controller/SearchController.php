<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Table;

use ReinventSoftware\UebusaitoBundle\Form\SearchFormType;

class SearchController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $utilityPrivate;
    private $query;
    private $ajax;
    private $table;
    
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
    * @Template("@UebusaitoBundleViews/render/module/search.html.twig")
    */
    public function moduleAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        $moduleRow = $this->query->selectModuleDatabase(5);
        
        $this->response['module']['id'] = $moduleRow['id'];
        $this->response['module']['label'] = $moduleRow['label'];
        
        // Form
        $form = $this->createForm(SearchFormType::class, null, Array(
            'validation_groups' => Array('search_module')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true) {
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
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->table = new Table($this->container, $this->entityManager);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        // Pagination
        $pageRows = $this->query->selectAllPagesDatabase($this->urlLocale, $this->urlExtra);
        
        $tableResult = $this->table->request($pageRows, 20, "searchRender", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->createListHtml($tableResult['list']);;
        
        $this->response['values']['results'] = $pageRows;
        
        if ($this->table->checkPost() == true) {
            $render = $this->renderView("@UebusaitoBundleViews/render/search.html.twig", Array(
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
    private function createListHtml($tableResult) {
        $listHtml = "";
        
        foreach ($tableResult as $key => $value) {
            $listHtml .= "<div class=\"box\">
                <p class=\"title\"><b>{$value['title']}</b></p>";
                if (strlen($value['argument']) > 200)
                    $listHtml .= "<p class=\"argument\">" . substr($value['argument'], 0, 200) . "...</p>";
                else
                    $listHtml .= "<p class=\"argument\">{$value['argument']}</p>
                <a href=\"{$this->utility->getUrlRoot()}{$this->utility->getWebsiteFile()}/{$this->urlLocale}/{$value['id']}\">{$this->utility->getTranslator()->trans("searchController_2")}</a>
            </div>";
        }
        
        return $listHtml;
    }
}