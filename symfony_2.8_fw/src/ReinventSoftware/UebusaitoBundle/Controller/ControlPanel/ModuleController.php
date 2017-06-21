<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Table;

use ReinventSoftware\UebusaitoBundle\Entity\Module;

use ReinventSoftware\UebusaitoBundle\Form\ModulesDragFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\ModulesDragModel;

use ReinventSoftware\UebusaitoBundle\Form\ModuleFormType;

use ReinventSoftware\UebusaitoBundle\Form\ModulesSelectionFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\ModulesSelectionModel;

class ModuleController extends Controller {
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
     * @Template("UebusaitoBundle:render:control_panel/modules_drag.html.twig")
     */
    public function dragAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        // Create form
        $modulesDragFormType = new ModulesDragFormType();
        $form = $this->createForm($modulesDragFormType, new ModulesDragModel(), Array(
            'validation_groups' => Array(
                'modules_drag'
            )
        ));
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $form->handleRequest($this->utility->getRequestStack());

            // Check form
            if ($form->isValid() == true) {
                $sortHeaderExplode = explode(",", $form->get("sortHeader")->getData());
                $sortLeftExplode = explode(",", $form->get("sortLeft")->getData());
                $sortCenterExplode = explode(",", $form->get("sortCenter")->getData());
                $sortRightExplode = explode(",", $form->get("sortRight")->getData());

                if (count($sortHeaderExplode) > 0) {
                    foreach ($sortHeaderExplode as $key => $value)
                        $this->modulesDatabase("sort", $value, $key, "header");
                }

                if (count($sortLeftExplode) > 0) {
                    foreach ($sortLeftExplode as $key => $value)
                        $this->modulesDatabase("sort", $value, $key, "left");
                }

                if (count($sortCenterExplode) > 0) {
                    foreach ($sortCenterExplode as $key => $value)
                        $this->modulesDatabase("sort", $value, $key, "center");
                }

                if (count($sortRightExplode) > 0) {
                    foreach ($sortRightExplode as $key => $value)
                        $this->modulesDatabase("sort", $value, $key, "right");
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
     * @Template("UebusaitoBundle:render:control_panel/module_creation.html.twig")
     */
    public function creationAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $moduleEntity = new Module();
        
        // Create form
        $moduleFormType = new ModuleFormType();
        $form = $this->createForm($moduleFormType, $moduleEntity, Array(
            'validation_groups' => Array(
                'module_creation'
            )
        ));
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $form->handleRequest($this->utility->getRequestStack());

            // Check form
            if ($form->isValid() == true) {
                if ($form->get("sort")->getData() == "" && $form->get("active")->getData() == "") {
                    $moduleRows = $this->query->selectAllModulesDatabase(null, $form->get("position")->getData());

                    $form->getData()->setSort(count($moduleRows));
                    $form->getData()->setActive(false);
                }

                // Insert in database
                $this->entityManager->persist($moduleEntity);
                $this->entityManager->flush();

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
     * @Template("UebusaitoBundle:render:control_panel/modules_selection.html.twig")
     */
    public function selectionAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->table = new Table($this->container, $this->entityManager);
        
        $this->response = Array();
        
        // Create form
        $modulesSelectionFormType = new ModulesSelectionFormType($this->container, $this->entityManager);
        $form = $this->createForm($modulesSelectionFormType, new ModulesSelectionModel(), Array(
            'validation_groups' => Array(
                'modules_selection'
            )
        ));
        
        // Pagination
        $moduleRows = $this->query->selectAllModulesDatabase();
        
        $tableResult = $this->table->request($moduleRows, 20, "module", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml($tableResult['list']);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $id = 0;
            
            $form->handleRequest($this->utility->getRequestStack());

            // Check form
            if ($form->isValid() == true) {
                $id = $form->get("id")->getData();

                $this->selectionResult($id);
            }
            else if ($this->utility->getRequestStack()->request->get("event") == null && $this->utilityPrivate->checkToken() == true) {
                $id = $this->utility->getRequestStack()->request->get("id") == "" ? 0 : $this->utility->getRequestStack()->request->get("id");

                $this->selectionResult($id);
            }
            else if (($this->utility->getRequestStack()->request->get("event") == "refresh" && $this->utilityPrivate->checkToken() == true) ||
                        $this->table->checkPost() == true) {
                $render = $this->renderView("UebusaitoBundle::render/control_panel/modules_selection_desktop.html.twig", Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));

                $this->response['render'] = $render;
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_5");
                $this->response['errors'] = $this->ajax->errors($form);
            }
            
            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $id,
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
     * @Template("UebusaitoBundle:render:control_panel/module_profile.html.twig")
     */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $moduleEntity = $this->entityManager->getRepository("UebusaitoBundle:Module")->find($this->urlExtra);
        
        // Create form
        $moduleFormType = new ModuleFormType();
        $form = $this->createForm($moduleFormType, $moduleEntity, Array(
            'validation_groups' => Array(
                'module_profile'
            )
        ));
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $form->handleRequest($this->utility->getRequestStack());

            // Check form
            if ($form->isValid() == true) {
                $sortExplode = Array();

                if ($form->get("position")->getData() == null && $form->get("sort")->getData() == null) {
                    $moduleRow = $this->query->selectModuleDatabase($this->urlExtra);

                    $form->getData()->setPosition($moduleRow['position']);
                    $form->getData()->setSort($moduleRow['sort']);
                }
                else {
                    $formDataSort = $form->get("sort")->getData();

                    if ($formDataSort != null)
                        $sortExplode = explode(",", $formDataSort);
                }

                // Update in database
                $this->entityManager->persist($moduleEntity);
                $this->entityManager->flush();

                if (count($sortExplode) > 0) {
                    foreach ($sortExplode as $key => $value)
                        $this->modulesDatabase("sort", $value, $key, null);
                }

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
     * @Template("UebusaitoBundle:render:control_panel/module_profile_sort.html.twig")
     */
    public function profileSortAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            // Check token
            if ($this->utilityPrivate->checkToken() == true) {
                $this->response['modules']['sort'] = $this->query->selectAllModulesDatabase($this->utility->getRequestStack()->request->get("id"), $this->utility->getRequestStack()->request->get("position"));

                $render = $this->renderView("UebusaitoBundle::render/control_panel/module_profile_sort.html.twig", Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));

                $this->response['render'] = $render;
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_8");
            
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
    
    /**
     * @Template("UebusaitoBundle:render:control_panel/module_deletion.html.twig")
     */
    public function deletionAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->table = new Table($this->container, $this->entityManager);
        
        $this->response = Array();
        
        // Pagination
        $moduleRows = $this->query->selectAllModulesDatabase();
        
        $tableResult = $this->table->request($moduleRows, 20, "module", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml($tableResult['list']);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            if ($this->utility->getRequestStack()->request->get("event") == "delete" && $this->utilityPrivate->checkToken() == true) {
                $modulesDatabase = $this->modulesDatabase("delete", $this->utility->getRequestStack()->request->get("id"), null, null);

                if ($modulesDatabase == true)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("moduleController_9");
            }
            else if ($this->utility->getRequestStack()->request->get("event") == "deleteAll" && $this->utilityPrivate->checkToken() == true) {
                $modulesDatabase = $this->modulesDatabase("deleteAll", null, null, null);

                if ($modulesDatabase == true) {
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/modules_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));

                    $this->response['render'] = $render;

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("moduleController_10");
                }
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
    private function selectionResult($id) {
        $moduleEntity = $this->entityManager->getRepository("UebusaitoBundle:Module")->find($id);
        
        if ($moduleEntity != null) {
            $this->response['modules']['position'] = $moduleEntity->getPosition();
            $this->response['modules']['sort'] = $this->query->selectAllModulesDatabase(null, $moduleEntity->getPosition());
            
            // Create form
            $moduleFormType = new ModuleFormType();
            $formModuleProfile = $this->createForm($moduleFormType, $moduleEntity, Array(
                'validation_groups' => Array(
                    'module_profile'
                )
            ));
            
            $render = $this->renderView("UebusaitoBundle::render/control_panel/module_profile.html.twig", Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $moduleEntity->getId(),
                'response' => $this->response,
                'form' => $formModuleProfile->createView()
            ));
            
            $this->response['render'] = $render;
        }
        else
            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_5");
    }
    
    private function listHtml($elements) {
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
                        $listHtml .= "<button class=\"cp_module_deletion btn btn-danger\"><i class=\"fa fa-remove\"></i></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function modulesDatabase($type, $id, $sort, $position) {
        if ($type == "sort") {
            if ($position == null) {
                $query = $this->utility->getConnection()->prepare("UPDATE modules
                                                                    SET sort = :sort
                                                                    WHERE id = :id");
            }
            else {
                $query = $this->utility->getConnection()->prepare("UPDATE modules
                                                                    SET position = :position,
                                                                        sort = :sort
                                                                    WHERE id = :id");
                
                $query->bindValue(":position", $position);
            }
            
            $query->bindValue(":sort", $sort);
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