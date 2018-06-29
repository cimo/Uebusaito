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

use ReinventSoftware\UebusaitoBundle\Entity\PageComment;

use ReinventSoftware\UebusaitoBundle\Form\PageCommentFormType;

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
    *   name = "page_comment_creation",
    *   path = "/page_comment_creation/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/page_comment.html.twig")
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
            'validation_groups' => Array('page_comment')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true) {
            if ($form->isValid() == true) {
                $pageCommentRows = $this->query->selectAllPageCommentDatabase($this->urlCurrentPageId);
                
                if ($pageRow['comment'] == true && $settingRow['page_comment'] == true && $settingRow['page_comment_active'] == true
                        && $pageCommentRows[count($pageCommentRows) - 1]['username'] != $this->getUser()->getUsername()) {
                    $pageCommentEntity->setPageId($this->urlCurrentPageId);
                    $pageCommentEntity->setUsername($this->getUser()->getUsername());

                    if (isset($_SESSION['usernameReply']) == true && $_SESSION['usernameReply'] != "")
                        $pageCommentEntity->setUsernameReply($_SESSION['usernameReply']);

                    $pageCommentEntity->setDateCreation(date("Y-m-d H:i:s"));

                    // Insert in database
                    $this->entityManager->persist($pageCommentEntity);
                    $this->entityManager->flush();

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageCommentController_1");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageCommentController_2");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageCommentController_3");
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
    *   name = "page_comment_render",
    *   path = "/page_comment_render/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/page_comment.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->tableAndPagination = new TableAndPagination($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        // Logic
        $pageRow = $this->query->selectPageDatabase($this->urlLocale, $this->urlCurrentPageId);
        
        $this->response['values']['pageRow'] = $pageRow;
        
        $_SESSION['usernameReply'] = "";
        
        $this->tableAndPaginationLogic();
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    /**
    * @Route(
    *   name = "page_comment_result",
    *   path = "/page_comment_result/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/page_comment.html.twig")
    */
    public function resultAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        $pageRow = $this->query->selectPageDatabase($this->urlLocale, $this->urlCurrentPageId);
        $settingRow = $this->query->selectSettingDatabase();
        
        $this->response['values']['pageRow'] = $pageRow;
        
        if ($request->isMethod("POST") == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "reply" && $pageRow['comment'] == true && $settingRow['page_comment_active'] == true) {
                    $pageCommentRow = $this->query->selectPageCommentDatabase($request->get("id"));

                    $_SESSION['usernameReply'] = $pageCommentRow['username'];

                    $this->response['values']['usernameReply'] = $_SESSION['usernameReply'];
                    $this->response['values']['argumentReply'] = $pageCommentRow['argument'];
                }
                else if ($request->get("event") == "modify" && $pageRow['comment'] == true && $settingRow['page_comment_active'] == true) {
                    $pageCommentDatabase = $this->pageCommentDatabase($request->get("id"), $request->get("content"));

                    if ($pageCommentDatabase == true) {
                        $this->tableAndPaginationLogic();

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageCommentController_4");
                    }
                }
                else
                    $this->tableAndPaginationLogic();
            }
        }
        
        return $this->ajax->response(Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        ));
    }
    
    // Functions private
    private function tableAndPaginationLogic() {
        $pageCommentRows = $this->query->selectAllPageCommentDatabase($this->urlCurrentPageId);

        $tableAndPagination = $this->tableAndPagination->request($pageCommentRows, 20, "pageComment", false, true);

        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = count($tableAndPagination['listHtml']);
    }
    
    private function createListHtml($elements) {
        $setting = $this->query->selectSettingDatabase();
        
        $listHtml = "";
        
        $count = count($elements);
        
        foreach ($elements as $key => $value) {
            $listHtml .= "<div>
                <div class=\"horizontal_center\">
                    <p>{$value['username']}</p>";
                    
                    if (file_exists("{$this->utility->getPathSrcBundle()}/Resources/public/files/{$value['username']}/Avatar.jpg") == true)
                        $listHtml .= "<img src=\"{$this->utility->getUrlRoot()}/bundles/uebusaito/files/{$value['username']}/Avatar.jpg\"/>";
                    else
                        $listHtml .= "<img src=\"{$this->utility->getUrlRoot()}/bundles/uebusaito/images/templates/{$setting['template']}/no_avatar.jpg\"/>";
                    
                    if ($value['username_reply'] != null) {
                        if (file_exists("{$this->utility->getPathSrcBundle()}/Resources/public/files/{$value['username_reply']}/Avatar.jpg") == true)
                            $listHtml .= "<img class=\"avatar_reply\" src=\"{$this->utility->getUrlRoot()}/bundles/uebusaito/files/{$value['username_reply']}/Avatar.jpg\"/>";
                        else
                            $listHtml .= "<img class=\"avatar_reply\" src=\"{$this->utility->getUrlRoot()}/bundles/uebusaito/images/templates/{$setting['template']}/no_avatar.jpg\"/>";
                    }
                    
                $listHtml .= "</div>
                <div>
                    <div class=\"message\">
                        <p contenteditable=\"false\" class=\"argument item_{$value['id']}\">{$value['argument']}</p>
                        <div class=\"info\">";
                            
                            if (strpos($value['date_creation'], "0000") === false && strpos($value['date_modification'], "0000") !== false) {
                                $dateFormat = $this->utility->dateFormat($value['date_creation']);
                                
                                $listHtml .= "<p><i class=\"fa fa-calendar\"></i> <b>{$this->utility->getTranslator()->trans("pageCommentController_5")}</b>{$dateFormat[0]} [{$dateFormat[1]}]</p>";
                            }
                            else {
                                $dateFormat = $this->utility->dateFormat($value['date_modification']);
                                
                                $listHtml .= "<p><i class=\"fa fa-calendar\"></i> <b>{$this->utility->getTranslator()->trans("pageCommentController_6")}</b>{$dateFormat[0]} [{$dateFormat[1]}]</p>";
                            }
                            
                        $listHtml .= "</div>
                    </div>
                    <div class=\"action\">";
                        if ($this->getUser() != null) {
                            if ($key == ($count - 1) && $this->getUser()->getUsername() != $elements[$count - 1]['username']) {
                                $listHtml .= "<i class=\"fa fa-pencil\"></i>
                                <a class=\"button_reply\" class=\"display_inline_block margin_bottom\" href=\"#\">
                                    {$this->utility->getTranslator()->trans("pageCommentController_7")}
                                </a>";
                            }

                            if ($this->getUser()->getUsername() == $value['username']) {
                                $listHtml .= "<i class=\"fa fa-pencil-square-o\"></i>
                                <a class=\"button_modify\" class=\"display_inline_block margin_bottom\" href=\"#\">
                                    {$this->utility->getTranslator()->trans("pageCommentController_8")}
                                </a>";
                            }
                        }
                    $listHtml .= "</div>
                </div>
            </div>";
        }
        
        return $listHtml;
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