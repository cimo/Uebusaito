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

use ReinventSoftware\UebusaitoBundle\Entity\Module;

use ReinventSoftware\UebusaitoBundle\Form\ModuleDragFormType;
use ReinventSoftware\UebusaitoBundle\Form\ModuleFormType;
use ReinventSoftware\UebusaitoBundle\Form\ModuleSelectionFormType;

class ModuleController extends Controller {
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
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_module_drag",
    *   path = "/cp_module_drag/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/module_drag.html.twig")
    */
    public function dragAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkRoleUser = $this->uebusaitoUtility->checkRoleUser(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $form = $this->createForm(ModuleDragFormType::class, null, Array(
            'validation_groups' => Array('module_drag')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkRoleUser == true) {
            if ($form->isValid() == true) {
                $sortHeaderExplode = explode(",", $form->get("sortHeader")->getData());
                $sortLeftExplode = explode(",", $form->get("sortLeft")->getData());
                $sortCenterExplode = explode(",", $form->get("sortCenter")->getData());
                $sortRightExplode = explode(",", $form->get("sortRight")->getData());

                if (count($sortHeaderExplode) > 0) {
                    foreach ($sortHeaderExplode as $key => $value)
                        $this->moduleDatabase("update", $value, $key + 1, "header");
                }

                if (count($sortLeftExplode) > 0) {
                    foreach ($sortLeftExplode as $key => $value)
                        $this->moduleDatabase("update", $value, $key + 1, "left");
                }

                if (count($sortCenterExplode) > 0) {
                    foreach ($sortCenterExplode as $key => $value)
                        $this->moduleDatabase("update", $value, $key + 1, "center");
                }

                if (count($sortRightExplode) > 0) {
                    foreach ($sortRightExplode as $key => $value)
                        $this->moduleDatabase("update", $value, $key + 1, "right");
                }

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("moduleController_1");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_2");
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
    *   name = "cp_module_creation",
    *   path = "/cp_module_creation/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/module_creation.html.twig")
    */
    public function creationAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkRoleUser = $this->uebusaitoUtility->checkRoleUser(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $moduleEntity = new Module();
        
        $form = $this->createForm(ModuleFormType::class, $moduleEntity, Array(
            'validation_groups' => Array('module_creation'),
            'choicesPositionInColumn' => Array()
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkRoleUser == true) {
            if ($form->isValid() == true) {
                $moduleEntity->setActive(false);

                // Insert in database
                $this->entityManager->persist($moduleEntity);
                $this->entityManager->flush();
                
                $this->updatePositionInColumnDatabase($form->get("sort")->getData(), $moduleEntity->getId());

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("moduleController_3");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_4");
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
    *   name = "cp_module_selection",
    *   path = "/cp_module_selection/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/module_selection.html.twig")
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
        
        $checkRoleUser = $this->uebusaitoUtility->checkRoleUser(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $_SESSION['module_profile_id'] = 0;
        
        $moduleRows = $this->query->selectAllModuleDatabase();
        
        $tableAndPagination = $this->tableAndPagination->request($moduleRows, 20, "module", true, true);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['list']);
        
        $form = $this->createForm(ModuleSelectionFormType::class, null, Array(
            'validation_groups' => Array('module_selection'),
            'choicesId' => array_reverse(array_column($moduleRows, "id", "name"), true)
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkRoleUser == true && $this->utility->checkToken($request) == true) {
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
    *   name = "cp_module_profile_result",
    *   path = "/cp_module_profile_result/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/module_profile.html.twig")
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
        
        $checkRoleUser = $this->uebusaitoUtility->checkRoleUser(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkRoleUser == true) {
            $id = 0;
            
            if (empty($request->get("id")) == false)
                $id = $request->get("id");
            else if (empty($request->get("form_module_selection")['id']) == false)
                $id = $request->get("form_module_selection")['id'];
            
            $moduleEntity = $this->entityManager->getRepository("UebusaitoBundle:Module")->find($id);
            
            if ($moduleEntity != null) {
                $_SESSION['module_profile_id'] = $id;
                
                $form = $this->createForm(ModuleFormType::class, $moduleEntity, Array(
                    'validation_groups' => Array('module_profile'),
                    'choicesPositionInColumn' => array_column($this->query->selectAllModuleDatabase(null, $moduleEntity->getPosition()), "id", "name")
                ));
                $form->handleRequest($request);
                
                $this->response['values']['id'] = $_SESSION['module_profile_id'];
                
                $this->response['render'] = $this->renderView("@UebusaitoBundleViews/render/control_panel/module_profile.html.twig", Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response,
                    'form' => $form->createView()
                ));
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_5");
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
    *   name = "cp_module_profile_sort",
    *   path = "/cp_module_profile_sort/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/module_profile.html.twig")
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
        
        $checkRoleUser = $this->uebusaitoUtility->checkRoleUser(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkRoleUser == true && $this->utility->checkToken($request) == true) {
            $this->response['values']['moduleRows'] = array_column($this->query->selectAllModuleDatabase(null, $request->get("position")), "id", "name");
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
    *   name = "cp_module_profile_save",
    *   path = "/cp_module_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/module_profile.html.twig")
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
        
        $checkRoleUser = $this->uebusaitoUtility->checkRoleUser(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $moduleEntity = $this->entityManager->getRepository("UebusaitoBundle:Module")->find($_SESSION['module_profile_id']);
        
        $form = $this->createForm(ModuleFormType::class, $moduleEntity, Array(
            'validation_groups' => Array('module_profile'),
            'choicesPositionInColumn' => array_column($this->query->selectAllModuleDatabase(null, $moduleEntity->getPosition()), "id", "name")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkRoleUser == true) {
            if ($form->isValid() == true) {
                // Update in database
                $this->entityManager->persist($moduleEntity);
                $this->entityManager->flush();
                
                $this->updatePositionInColumnDatabase($form->get("sort")->getData(), $moduleEntity->getId());

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("moduleController_6");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_7");
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
    *   name = "cp_module_deletion",
    *   path = "/cp_module_deletion/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/module_deletion.html.twig")
    */
    public function deletionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkRoleUser = $this->uebusaitoUtility->checkRoleUser(Array("ROLE_ADMIN"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkRoleUser == true && $this->utility->checkToken($request) == true) {
            if ($request->get("event") == "delete") {
                $id = $request->get("id") == null ? $_SESSION['module_profile_id'] : $request->get("id");
                
                $moduleDatabase = $this->moduleDatabase("delete", $id, null, null);

                if ($moduleDatabase == true) {
                    $this->response['values']['id'] = $id;
                    
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("moduleController_9");
                }
            }
            else if ($request->get("event") == "deleteAll") {
                $moduleDatabase = $this->moduleDatabase("deleteAll", null, null, null);

                if ($moduleDatabase == true)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("moduleController_10");
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_11");
            
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
    private function createListHtml($elements) {
        $listHtml = "";
        
        foreach ($elements as $key => $value) {
            $listHtml .= "<tr>
                <td class=\"id_column\">
                    {$value['id']}
                </td>
                <td class=\"checkbox_column\">
                    <input class=\"display_inline margin_clear\" type=\"checkbox\"/>
                </td>
                <td>
                    {$value['name']}
                </td>
                <td>
                    {$value['file_name']}
                </td>
                <td>";
                    if ($value['active'] == 0)
                        $listHtml .= $this->utility->getTranslator()->trans("moduleController_12");
                    else
                        $listHtml .= $this->utility->getTranslator()->trans("moduleController_13");
                $listHtml .= "</td>
                <td class=\"horizontal_center\">";
                    if ($value['id'] > 5)
                        $listHtml .= "<button class=\"cp_module_deletion button_custom_danger\"><i class=\"fa fa-remove\"></i></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function updatePositionInColumnDatabase($sort, $moduleId) {
        $sortExplode = explode(",", $sort);
        array_pop($sortExplode);
        
        foreach ($sortExplode as $key => $value) {
            if ($value == "")
                $value = $moduleId;

            $query = $this->utility->getConnection()->prepare("UPDATE modules
                                                                SET position_in_column = :positionInColumn
                                                                WHERE id = :id");

            $query->bindValue(":positionInColumn", $key + 1);
            $query->bindValue(":id", $value);

            $query->execute();
        }
    }
    
    private function moduleDatabase($type, $id, $positionInColumn, $position) {
        if ($type == "update") {
            $query = $this->utility->getConnection()->prepare("UPDATE modules
                                                                SET position = :position,
                                                                    position_in_column = :positionInColumn
                                                                WHERE id = :id");
                
            $query->bindValue(":position", $position);
            $query->bindValue(":positionInColumn", $positionInColumn);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM modules
                                                                WHERE id > :idExclude
                                                                AND id = :id");

            $query->bindValue(":idExclude", 4);
            $query->bindValue(":id", $id);

            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM modules
                                                                WHERE id > :idExclude");

            $query->bindValue(":idExclude", 4);

            return $query->execute();
        }
    }
}