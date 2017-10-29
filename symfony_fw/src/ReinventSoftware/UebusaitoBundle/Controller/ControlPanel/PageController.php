<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UebusaitoUtility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\TableAndPagination;

use ReinventSoftware\UebusaitoBundle\Entity\Page;

use ReinventSoftware\UebusaitoBundle\Form\PageFormType;
use ReinventSoftware\UebusaitoBundle\Form\PagesSelectionFormType;

class PageController extends Controller {
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
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/page_creation.html.twig")
    */
    public function creationAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Logic
        $pageEntity = new Page();
        
        $pageRows = $this->query->selectAllPagesDatabase($this->urlLocale);
        
        $form = $this->createForm(PageFormType::class, $pageEntity, Array(
            'validation_groups' => Array('page_creation'),
            'urlLocale' => $this->urlLocale,
            'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId()),
            'choicesParent' => array_flip($this->uebusaitoUtility->createPagesList($pageRows, true)),
            'choicesPositionInMenu' => array_column($this->query->selectAllPageParentDatabase(null), "id", "alias")
        ));
        $form->handleRequest($request);
        
        $this->response['values']['rolesSelect'] = $this->uebusaitoUtility->createHtmlRoles("form_page_roleId_field", true);
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($form->isValid() == true) {
                // Insert in database
                $this->entityManager->persist($pageEntity);
                $this->entityManager->flush();

                $pagesDatabase = $this->pagesDatabase("insert", null, $this->urlLocale, $form);

                if ($pagesDatabase == true) {
                    $this->updatePositionInMenuDatabase($form->get("sort")->getData(), $pageEntity->getId());
                    
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
    *   name = "cp_pages_selection",
    *   path = "/cp_pages_selection/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/pages_selection.html.twig")
    */
    public function selectionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->tableAndPagination = new TableAndPagination($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Logic
        $_SESSION['page_profile_id'] = 0;
        
        $pageRows = $this->query->selectAllPagesDatabase($this->urlLocale);
        
        $tableAndPagination = $this->tableAndPagination->request($pageRows, 20, "page", false, true);
        
        $this->listHtml = "";
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['list'] = $this->createHtmlList($tableAndPagination['list']);
        
        $form = $this->createForm(PagesSelectionFormType::class, null, Array(
            'validation_groups' => Array('pages_selection'),
            'choicesId' => array_flip($this->uebusaitoUtility->createPagesList($pageRows, true))
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($this->utility->checkToken($request) == true) {
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
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/page_profile.html.twig")
    */
    public function profileResultAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Logic
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            $id = 0;
            
            if (empty($request->get("id")) == false)
                $id = $request->get("id");
            else if (empty($request->get("form_pages_selection")['id']) == false)
                $id = $request->get("form_pagess_selection")['id'];
            
            $pageEntity = $this->entityManager->getRepository("UebusaitoBundle:Page")->find($id);
            
            if ($pageEntity != null) {
                $_SESSION['page_profile_id'] = $id;
                
                $pageRows = $this->query->selectAllPagesDatabase($this->urlLocale);
                
                $form = $this->createForm(PageFormType::class, $pageEntity, Array(
                    'validation_groups' => Array('page_profile'),
                    'urlLocale' => $this->urlLocale,
                    'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId()),
                    'choicesParent' => array_flip($this->uebusaitoUtility->createPagesList($pageRows, true)),
                    'choicesPositionInMenu' => array_column($this->query->selectAllPageParentDatabase($pageEntity->getParent()), "id", "alias")
                ));
                $form->handleRequest($request);
                
                $this->response['values']['rolesSelect'] = $this->uebusaitoUtility->createHtmlRoles("form_page_roleId_field", true);
                $this->response['values']['id'] = $_SESSION['page_profile_id'];
                
                $this->response['render'] = $this->renderView("@UebusaitoBundleViews/render/control_panel/page_profile.html.twig", Array(
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
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/page_profile.html.twig")
    */
    public function profileSortAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Logic
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($this->utility->checkToken($request) == true)
                $this->response['values']['pageRows'] = array_column($this->query->selectAllPageParentDatabase($request->get("id")), "id", "alias");
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
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/page_profile.html.twig")
    */
    public function profileSaveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Logic
        $pageEntity = $this->entityManager->getRepository("UebusaitoBundle:Page")->find($_SESSION['page_profile_id']);
        
        $pageRows = $this->query->selectAllPagesDatabase($this->urlLocale);
        
        $form = $this->createForm(PageFormType::class, $pageEntity, Array(
            'validation_groups' => Array('page_profile'),
            'urlLocale' => $this->urlLocale,
            'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId()),
            'choicesParent' => array_flip($this->uebusaitoUtility->createPagesList($pageRows, true)),
            'choicesPositionInMenu' => array_column($this->query->selectAllPageParentDatabase($pageEntity->getParent()), "id", "alias")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($form->isValid() == true) {
                // Update in database
                $this->entityManager->persist($pageEntity);
                $this->entityManager->flush();

                $pagesDatabase = $this->pagesDatabase("update", $pageEntity->getId(), null, $form);

                if ($pagesDatabase == true) {
                    $this->updatePositionInMenuDatabase($form->get("sort")->getData(), $pageEntity->getId());

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
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/page_deletion.html.twig")
    */
    public function deletionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        // Logic
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($request->get("event") == "delete" && $this->utility->checkToken($request) == true) {
                $id = $request->get("id") == null ? $_SESSION['page_profile_id'] : $request->get("id");

                $pageChildrenRows = $this->query->selectAllPageChildrenDatabase($id);

                if ($pageChildrenRows == false) {
                    $pagesDatabase = $this->pagesDatabase("delete", $id, null, null);

                    if ($pagesDatabase == true) {
                        $this->response['values']['id'] = $id;
                    
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_6");
                    }
                }
                else {
                    // Popup
                    $this->response['values']['id'] = $id;
                    $this->response['values']['text'] = "<p class=\"margin_bottom\">" . $this->utility->getTranslator()->trans("pageController_7") . "</p>";
                    $this->response['values']['button'] = "<button id=\"cp_page_deletion_parent_all\" class=\"margin_bottom\">" . $this->utility->getTranslator()->trans("pageController_8") . "</button>";
                    $this->response['values']['select'] = $this->uebusaitoUtility->createHtmlPages($this->urlLocale, "cp_page_deletion_parent_new");
                }
            }
            else if ($request->get("event") == "deleteAll" && $this->utility->checkToken($request) == true) {
                $pagesDatabase = $this->pagesDatabase("deleteAll", null, null, null);

                if ($pagesDatabase == true)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_9");
            }
            else if ($request->get("event") == "parentAll" && $this->utility->checkToken($request) == true) {
                $id = $request->get("id") == null ? $_SESSION['page_profile_id'] : $request->get("id");
                
                $this->removedId = Array();

                $this->removePageChildrenDatabase($id);
                
                array_unshift($this->removedId, $id);

                $pagesDatabase = $this->pagesDatabase("delete", $id, null, null);

                if ($pagesDatabase == true) {
                    $this->response['values']['removedId'] = $this->removedId;
                    
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_9");
                }
            }
            else if ($request->get("event") == "parentNew" && $this->utility->checkToken($request) == true) {
                $id = $request->get("id") == null ? $_SESSION['page_profile_id'] : $request->get("id");

                $this->updatePageChildrenDatabase($id, $request->get("parentNew"));

                $pagesDatabase = $this->pagesDatabase("delete", $id, null, null);

                if ($pagesDatabase == true) {
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
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    // Functions private    
    private function createHtmlList($elements) {
        foreach ($elements as $key => $value) {
            $this->listHtml .= "<tr>
                <td class=\"id_column\">
                    {$value['id']}
                </td>
                <td class=\"checkbox_column\">
                    <input class=\"display_inline margin_clear\" type=\"checkbox\"/>
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
                <td class=\"horizontal_center\">";
                    if ($value['id'] > 5)
                        $this->listHtml .= "<button class=\"cp_page_deletion button_custom_danger\"><i class=\"fa fa-remove\"></i></button>
                </td>
            </tr>";
            
            if (count($value['children']) > 0)
                $this->createHtmlList($value['children']);
        }
        
        return $this->listHtml;
    }
    
    private function removePageChildrenDatabase($id) {
        $pageChildrenRows = $this->query->selectAllPageChildrenDatabase($id);
        
        for ($i = 0; $i < count($pageChildrenRows); $i ++) {
            $this->removedId[] = $pageChildrenRows[$i]['id'];
                    
            $this->removePageChildrenDatabase($pageChildrenRows[$i]['id']);
        }
        
        $this->pagesDatabase("delete", $id, null, null);
    }
    
    private function updatePageChildrenDatabase($id, $parentNew) {
        $query = $this->utility->getConnection()->prepare("UPDATE pages
                                                            SET parent = :parentNew
                                                            WHERE parent = :id");
        
        $query->bindValue(":parentNew", $parentNew);
        $query->bindValue(":id", $id);
        
        $query->execute();
    }
    
    private function updatePositionInMenuDatabase($sort, $pageId) {
        $sortExplode = explode(",", $sort);
        array_pop($sortExplode);
        
        foreach ($sortExplode as $key => $value) {
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
    
    private function pagesDatabase($type, $id, $urlLocale, $form) {
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