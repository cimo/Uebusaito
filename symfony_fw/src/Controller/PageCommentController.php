<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Entity\PageComment;

use App\Form\PageCommentFormType;

class PageCommentController extends Controller {
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
    *   name = "pageComment_render",
    *   path = "/pageComment_render/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/pageComment.html.twig")
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
        $this->response['values']['pageRow'] = $this->query->selectPageDatabase($this->urlLocale, $this->urlCurrentPageId);
        
        $pageCommentRows = $this->query->selectAllPageCommentDatabase($this->urlCurrentPageId);

        $tableAndPagination = $this->tableAndPagination->request($pageCommentRows, 20, "pageComment", false, true);

        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        if ($this->tableAndPagination->checkPost() == true) {
            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response
            ));
        }
        else
            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageCommentController_1");
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    /**
    * @Route(
    *   name = "pageComment_creation",
    *   path = "/pageComment_creation/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/pageComment.html.twig")
    */
    public function creationAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        $pageRow = $this->query->selectPageDatabase($this->urlLocale, $this->urlCurrentPageId);
        $settingRow = $this->query->selectSettingDatabase();
        
        $this->response['values']['pageRow'] = $pageRow;
        
        if ($this->getUser() != null)
            $this->response['values']['username'] = $this->getUser()->getUsername();
        
        $pageCommentEntity = new PageComment();
        
        $form = $this->createForm(PageCommentFormType::class, $pageCommentEntity, Array(
            'validation_groups' => Array('pageComment')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true) {
            if ($form->isValid() == true) {
                $pageCommentRows = $this->query->selectAllPageCommentDatabase($this->urlCurrentPageId);
                
                if ($pageRow['comment'] == true && $settingRow['pageComment'] == true && $settingRow['pageComment_active'] == true
                        && $pageCommentRows[count($pageCommentRows) - 1]['username'] != $this->getUser()->getUsername()) {
                    $pageCommentEntity->setPageId($this->urlCurrentPageId);
                    $pageCommentEntity->setUsername($this->getUser()->getUsername());

                    if (isset($_SESSION['usernameReply']) == true && $_SESSION['usernameReply'] != "")
                        $pageCommentEntity->setUsernameReply($_SESSION['usernameReply']);

                    $pageCommentEntity->setDateCreation(date("Y-m-d H:i:s"));

                    // Insert in database
                    $this->entityManager->persist($pageCommentEntity);
                    $this->entityManager->flush();

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageCommentController_2");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageCommentController_3");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageCommentController_4");
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
    private function createListHtml($elements) {
        $setting = $this->query->selectSettingDatabase();
        
        $html = "<ul class=\"mdc-list mdc-list--two-line mdc-list--avatar-list\">";
        
        $elementsCount = count($elements);
        
        foreach ($elements as $key => $value) {
            $html .= "<li class=\"mdc-list-item\">";
                if (file_exists("{$this->utility->getPathWeb()}/files/{$value['username']}/Avatar.jpg") == true)
                    $html .= "<img class=\"mdc-list-item__graphic\" src=\"{$this->utility->getUrlRoot()}/files/{$value['username']}/Avatar.jpg\" aria-hidden=\"true\"/>";
                else
                    $html .= "<img class=\"mdc-list-item__graphic\" src=\"{$this->utility->getUrlRoot()}/images/templates/{$setting['template']}/no_avatar.jpg\" aria-hidden=\"true\"/>";
                
                $detail = "";
                
                if (strpos($value['date_creation'], "0000") === false && strpos($value['date_modification'], "0000") !== false) {
                    $dateFormat = $this->utility->dateFormat($value['date_creation']);
                    
                    $detail = $this->utility->getTranslator()->trans("pageCommentController_5") . "{$dateFormat[0]} [{$dateFormat[1]}]";
                }
                else {
                    $dateFormat = $this->utility->dateFormat($value['date_modification']);
                    
                    $detail = $this->utility->getTranslator()->trans("pageCommentController_6") . "{$dateFormat[0]} [{$dateFormat[1]}]";
                }
                
                $html .= "<span class=\"mdc-list-item__text\">
                    $detail
                    <span class=\"mdc-list-item__secondary-text\">{$value['argument']}</span>
                </span>";

                if ($this->getUser() != null) {
                    if ($this->getUser()->getUsername() != $value['username']) {
                        $row = $this->query->selectPageCommentDatabase($value['id'], $this->getUser()->getUsername());
                        
                        if ($row == false)
                            $html .= "<span class=\"mdc-list-item__meta material-icons\" aria-hidden=\"true\">reply</span>";
                    }
                    else {
                        if ($key == $elementsCount - 1)
                            $html .= "<span class=\"mdc-list-item__meta material-icons\" aria-hidden=\"true\">edit</span>";
                    }
                }

            $html .= "</li>";
            
            if ($key < $elementsCount - 1)
                $html .= "<li role=\"separator\" class=\"mdc-list-divider\"></li>";
        }
        
        $html .= "</ul>";
        
        return $html;
    }
    
    private function pageCommentDatabase($id, $argument) {
        $query = $this->utility->getConnection()->prepare("UPDATE pages_comments
                                                            SET argument = :argument,
                                                                date_modification = :dateModification
                                                            WHERE id = :id
                                                            AND username = :username");

        $query->bindValue(":argument", $argument);
        $query->bindValue(":dateModification", date("Y-m-d H:i:s"));
        $query->bindValue(":id", $id);
        $query->bindValue(":username", $this->getUser()->getUsername());

        return $query->execute();
    }
}