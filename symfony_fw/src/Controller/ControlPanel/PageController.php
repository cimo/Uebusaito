<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Entity\Page;

use App\Form\PageFormType;
use App\Form\PageSelectionFormType;

class PageController extends Controller {
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
    
    private $listHtml;
    private $removedId;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_page_creation",
    *   path = "/cp_page_creation/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
	*	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_creation.html.twig")
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $pageEntity = new Page();
        
        $_SESSION['pageProfileId'] = 0;
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale);
        
        $form = $this->createForm(PageFormType::class, $pageEntity, Array(
            'validation_groups' => Array('page_creation'),
            'urlLocale' => $this->urlLocale,
            'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId()),
            'choicesParent' => array_flip($this->utility->createPageList($pageRows, true))
        ));
        $form->handleRequest($request);
        
        $this->response['values']['userRoleSelectHtml'] = $this->utility->createUserRoleSelectHtml("form_page_roleUserId_select", true);
        $this->response['values']['pageSortListHtml'] = $this->utility->createPageSortListHtml();
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isValid() == true) {
                $pageEntity->setDateCreation(date("Y-m-d H:i:s"));
                
                // Insert in database
                $this->entityManager->persist($pageEntity);
                $this->entityManager->flush();

                $pageDatabase = $this->pageDatabase("insert", null, $this->urlLocale, $form);

                if ($pageDatabase == true) {
                    $this->updatePositionInMenuDatabase($form->get("menuSort")->getData(), $pageEntity->getId());
                    
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_1");
                }
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageController_2");
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
    *   name = "cp_page_selection",
    *   path = "/cp_page_selection/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
	*	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_selection.html.twig")
    */
    public function selectionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $_SESSION['pageProfileId'] = 0;
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale);
        
        $tableAndPagination = $this->tableAndPagination->request($pageRows, 20, "page", false, true);
        
        $this->listHtml = "";
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = count($tableAndPagination['listHtml']);
        
        $form = $this->createForm(PageSelectionFormType::class, null, Array(
            'validation_groups' => Array('page_selection'),
            'choicesId' => array_flip($this->utility->createPageList($pageRows, true))
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                return $this->ajax->response(Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));
            }
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
    *   name = "cp_page_profile_result",
    *   path = "/cp_page_profile_result/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
	*	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_profile.html.twig")
    */
    public function profileResultAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true
                    || $this->isCsrfTokenValid("intention", $request->get("form_page_selection")['_token']) == true) {
                $id = 0;

                if (empty($request->get("id")) == false)
                    $id = $request->get("id");
                else if (empty($request->get("form_page_selection")['id']) == false)
                    $id = $request->get("form_page_selection")['id'];

                $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($id);

                if ($pageEntity != null) {
                    $_SESSION['pageProfileId'] = $id;

                    $pageRows = $this->query->selectAllPageDatabase($this->urlLocale);

                    $form = $this->createForm(PageFormType::class, $pageEntity, Array(
                        'validation_groups' => Array('page_profile'),
                        'urlLocale' => $this->urlLocale,
                        'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId()),
                        'choicesParent' => array_flip($this->utility->createPageList($pageRows, true))
                    ));
                    $form->handleRequest($request);

                    $this->response['values']['userRoleSelectHtml'] = $this->utility->createUserRoleSelectHtml("form_page_roleUserId_select", true);
                    $this->response['values']['pageSortListHtml'] = $this->utility->createPageSortListHtml();
                    $this->response['values']['id'] = $_SESSION['pageProfileId'];
                    $this->response['values']['userCreation'] = $pageEntity->getUserCreation();
                    $this->response['values']['dateCreation'] = $this->utility->dateFormat($pageEntity->getDateCreation());
                    $this->response['values']['userModification'] = $pageEntity->getUserModification();
                    $this->response['values']['dateModification'] = $this->utility->dateFormat($pageEntity->getDateModification());

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/page_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $form->createView()
                    ));
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageController_3");
            }
        }
        
        return $this->ajax->response(Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        ));
    }
    
    /**
    * @Route(
    *   name = "cp_page_profile_sort",
    *   path = "/cp_page_profile_sort/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
	*	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_profile.html.twig")
    */
    public function profileSortAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $rows = array_column($this->query->selectAllPageParentDatabase($request->get("id")), "alias", "id");
                
                $this->response['values']['pageSortListHtml'] = $this->utility->createPageSortListHtml($rows);
            } 
        }
        
        return $this->ajax->response(Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        ));
    }
    
    /**
    * @Route(
    *   name = "cp_page_profile_save",
    *   path = "/cp_page_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
	*	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_profile.html.twig")
    */
    public function profileSaveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($_SESSION['pageProfileId']);
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale);
        
        $form = $this->createForm(PageFormType::class, $pageEntity, Array(
            'validation_groups' => Array('page_profile'),
            'urlLocale' => $this->urlLocale,
            'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId()),
            'choicesParent' => array_flip($this->utility->createPageList($pageRows, true))
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isValid() == true) {
                $pageEntity->setUserModification($this->getUser()->getUsername());
                $pageEntity->setDateModification(date("Y-m-d H:i:s"));
                
                // Update in database
                $this->entityManager->persist($pageEntity);
                $this->entityManager->flush();

                $pageDatabase = $this->pageDatabase("update", $pageEntity->getId(), null, $form);

                if ($pageDatabase == true) {
                    $this->updatePositionInMenuDatabase($form->get("menuSort")->getData(), $pageEntity->getId());

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_4");
                }
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageController_5");
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
    *   name = "cp_page_deletion",
    *   path = "/cp_page_deletion/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
	*	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_deletion.html.twig")
    */
    public function deletionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $_SESSION['pageProfileId'] : $request->get("id");

                    $pageChildrenRows = $this->query->selectAllPageChildrenDatabase($id);

                    if ($pageChildrenRows == false) {
                        $pageDatabase = $this->pageDatabase("delete", $id, null, null);

                        if ($pageDatabase == true) {
                            $this->response['values']['id'] = $id;

                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_6");
                        }
                    }
                    else {
                        // Popup
                        $this->response['values']['id'] = $id;
                        $this->response['values']['text'] = "<p class=\"margin_bottom\">" . $this->utility->getTranslator()->trans("pageController_7") . "</p>";
                        $this->response['values']['button'] = "<button id=\"cp_page_deletion_parent_all\" class=\"margin_bottom\">" . $this->utility->getTranslator()->trans("pageController_8") . "</button>";
                        $this->response['values']['pageSelectHtml'] = $this->utility->createPageSelectHtml($this->urlLocale, "cp_page_deletion_parent_new");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $pageDatabase = $this->pageDatabase("deleteAll", null, null, null);

                    if ($pageDatabase == true)
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_9");
                }
                else if ($request->get("event") == "parentAll") {
                    $id = $request->get("id") == null ? $_SESSION['pageProfileId'] : $request->get("id");

                    $this->removedId = Array();

                    $this->removePageChildrenDatabase($id);

                    array_unshift($this->removedId, $id);

                    $pageDatabase = $this->pageDatabase("delete", $id, null, null);

                    if ($pageDatabase == true) {
                        $this->response['values']['removedId'] = $this->removedId;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_9");
                    }
                }
                else if ($request->get("event") == "parentNew") {
                    $id = $request->get("id") == null ? $_SESSION['pageProfileId'] : $request->get("id");

                    $this->updatePageChildrenDatabase($id, $request->get("parentNew"));

                    $pageDatabase = $this->pageDatabase("delete", $id, null, null);

                    if ($pageDatabase == true) {
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_10");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageController_11");

                return $this->ajax->response(Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));
            }
        }
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    // Functions private    
    private function createListHtml($elements) {
        foreach ($elements as $key => $value) {
            $this->listHtml .= "<tr>
                <td class=\"id_column\">
                    {$value['id']}
                </td>
                <td class=\"checkbox_column\">
                    <div class=\"mdc-checkbox\">
                        <input class=\"mdc-checkbox__native-control\" type=\"checkbox\"/>
                        <div class=\"mdc-checkbox__background\">
                            <svg class=\"mdc-checkbox__checkmark\" viewBox=\"0 0 24 24\">
                                <path class=\"mdc-checkbox__checkmark-path\" fill=\"none\" stroke=\"white\" d=\"M1.73,12.91 8.1,19.28 22.79,4.59\"/>
                            </svg>
                            <div class=\"mdc-checkbox__mixedmark\"></div>
                        </div>
                    </div>
                </td>
                <td>
                    {$value['alias']}
                </td>
                <td>
                    {$value['title']}
                </td>
                <td>
                    {$value['menu_name']}
                </td>
                <td>";
                    if ($value['protected'] == 0)
                        $this->listHtml .= $this->utility->getTranslator()->trans("pageController_12");
                    else
                        $this->listHtml .= $this->utility->getTranslator()->trans("pageController_13");
                $this->listHtml .= "</td>
                    <td>";
                        if ($value['show_in_menu'] == 0)
                            $this->listHtml .= $this->utility->getTranslator()->trans("pageController_12");
                        else
                            $this->listHtml .= $this->utility->getTranslator()->trans("pageController_13");
                $this->listHtml .= "</td>
                    <td>";
                        if ($value['only_link'] == 0)
                            $this->listHtml .= $this->utility->getTranslator()->trans("pageController_12");
                        else
                            $this->listHtml .= $this->utility->getTranslator()->trans("pageController_13");
                $this->listHtml .= "</td>
                <td>";
                    if ($value['id'] > 5)
                        $this->listHtml .= "<button class=\"mdc-fab mdc-fab--mini cp_page_deletion\" type=\"button\" aria-label=\"Delete\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
            
            if (count($value['children']) > 0)
                $this->createListHtml($value['children']);
        }
        
        return $this->listHtml;
    }
    
    private function removePageChildrenDatabase($id) {
        $pageChildrenRows = $this->query->selectAllPageChildrenDatabase($id);
        
        for ($a = 0; $a < count($pageChildrenRows); $a ++) {
            $this->removedId[] = $pageChildrenRows[$a]['id'];
                    
            $this->removePageChildrenDatabase($pageChildrenRows[$a]['id']);
        }
        
        $this->pageDatabase("delete", $id, null, null);
    }
    
    private function updatePageChildrenDatabase($id, $parentNew) {
        $query = $this->utility->getConnection()->prepare("UPDATE pages
                                                            SET parent = :parentNew
                                                            WHERE parent = :id");
        
        $query->bindValue(":parentNew", $parentNew);
        $query->bindValue(":id", $id);
        
        $query->execute();
    }
    
    private function updatePositionInMenuDatabase($menuSort, $pageId) {
        $menuSortExplode = explode(",", $menuSort);
        array_pop($menuSortExplode);
        
        foreach ($menuSortExplode as $key => $value) {
            if ($value == "")
                $value = $pageId;

            $query = $this->utility->getConnection()->prepare("UPDATE pages
                                                                SET position_in_menu = :positionInMenu
                                                                WHERE id = :id");

            $query->bindValue(":positionInMenu", $key + 1);
            $query->bindValue(":id", $value);

            $query->execute();
        }
    }
    
    private function pageDatabase($type, $id, $urlLocale, $form) {
        if ($type == "insert") {
            $query = $this->utility->getConnection()->prepare("INSERT INTO pages_titles (
                                                                    pages_titles.$urlLocale
                                                                )
                                                                VALUES (
                                                                    :title
                                                                );
                                                                INSERT INTO pages_arguments (
                                                                    pages_arguments.$urlLocale
                                                                )
                                                                VALUES (
                                                                    :argument
                                                                );
                                                                INSERT INTO pages_menu_names (
                                                                    pages_menu_names.$urlLocale
                                                                )
                                                                VALUES (
                                                                    :menuName
                                                                );");
            
            $query->bindValue(":title", $form->get("title")->getData());
            
            $argumentHtmlEntities = htmlentities($form->get("argument")->getData(), ENT_QUOTES, "utf-8");
            $query->bindValue(":argument", $argumentHtmlEntities);
            
            $query->bindValue(":menuName", $form->get("menuName")->getData());
            
            return $query->execute();
        }
        else if ($type == "update") {
            $language = $form->get("language")->getData();
            
            $query = $this->utility->getConnection()->prepare("UPDATE pages_titles, pages_arguments, pages_menu_names
                                                                SET pages_titles.$language = :title,
                                                                    pages_arguments.$language = :argument,
                                                                    pages_menu_names.$language = :menuName
                                                                WHERE pages_titles.id = :id
                                                                AND pages_arguments.id = :id
                                                                AND pages_menu_names.id = :id");
            
            $query->bindValue(":title", $form->get("title")->getData());
            
            $argumentHtmlEntities = htmlentities($form->get("argument")->getData(), ENT_QUOTES, "utf-8");
            $query->bindValue(":argument", $argumentHtmlEntities);
            
            $query->bindValue(":menuName", $form->get("menuName")->getData());
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE pages, pages_titles, pages_arguments, pages_menu_names FROM pages, pages_titles, pages_arguments, pages_menu_names
                                                                WHERE pages.id > :idExclude
                                                                AND pages.id = :id
                                                                AND pages_titles.id = :id
                                                                AND pages_arguments.id = :id
                                                                AND pages_menu_names.id = :id");
            
            $query->bindValue(":idExclude", 5);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE pages, pages_titles, pages_arguments, pages_menu_names FROM pages, pages_titles, pages_arguments, pages_menu_names
                                                                WHERE pages.id > :idExclude
                                                                AND pages_titles.id > :idExclude
                                                                AND pages_arguments.id > :idExclude
                                                                AND pages_menu_names.id > :idExclude");
            
            $query->bindValue(":idExclude", 5);
            
            return $query->execute();
        }
    }
}