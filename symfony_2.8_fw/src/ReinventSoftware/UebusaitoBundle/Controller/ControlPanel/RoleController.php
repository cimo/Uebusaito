<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Table;

use ReinventSoftware\UebusaitoBundle\Entity\Role;

use ReinventSoftware\UebusaitoBundle\Form\RoleFormType;

use ReinventSoftware\UebusaitoBundle\Form\RolesSelectionFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\RolesSelectionModel;

class RoleController extends Controller {
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
     * @Template("UebusaitoBundle:render:control_panel/role_creation.html.twig")
     */
    public function creationAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $roleEntity = new Role();
        
        // Create form
        $roleFormType = new RoleFormType();
        $form = $this->createForm($roleFormType, $roleEntity, Array(
            'validation_groups' => Array(
                'role_creation'
            )
        ));
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $form->handleRequest($this->utility->getRequestStack());

            // Check form
            if ($form->isValid() == true) {
                // Insert in database
                $this->entityManager->persist($roleEntity);
                $this->entityManager->flush();

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("roleController_1");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("roleController_2");
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
     * @Template("UebusaitoBundle:render:control_panel/roles_selection.html.twig")
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
        $rolesSelectionFormType = new RolesSelectionFormType($this->container, $this->entityManager);
        $form = $this->createForm($rolesSelectionFormType, new RolesSelectionModel(), Array(
            'validation_groups' => Array(
                'roles_selection'
            )
        ));
        
        // Pagination
        $userRoleRows = $this->query->selectAllUserRolesDatabase();
        
        $tableResult = $this->table->request($userRoleRows, 20, "role", true, true);
        
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
                $render = $this->renderView("UebusaitoBundle::render/control_panel/roles_selection_desktop.html.twig", Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));

                $this->response['render'] = $render;
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("roleController_3");
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
     * @Template("UebusaitoBundle:render:control_panel/role_profile.html.twig")
     */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        $roleEntity = $this->entityManager->getRepository("UebusaitoBundle:Role")->find($this->urlExtra);
        
        // Create form
        $roleFormType = new RoleFormType();
        $form = $this->createForm($roleFormType, $roleEntity, Array(
            'validation_groups' => Array(
                'role_profile'
            )
        ));
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $form->handleRequest($this->utility->getRequestStack());

            // Check form
            if ($form->isValid() == true) {
                // Update in database
                $this->entityManager->persist($roleEntity);
                $this->entityManager->flush();

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("roleController_4");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("roleController_5");
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
     * @Template("UebusaitoBundle:render:control_panel/role_deletion.html.twig")
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
        $userRoleRows = $this->query->selectAllUserRolesDatabase();
        
        $tableResult = $this->table->request($userRoleRows, 20, "role", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml($tableResult['list']);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            if ($this->utility->getRequestStack()->request->get("event") == "delete" && $this->utilityPrivate->checkToken() == true) {
                $rolesDatabase = $this->rolesDatabase("delete", $this->utility->getRequestStack()->request->get("id"));

                if ($rolesDatabase == true)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("roleController_6");
            }
            else if ($this->utility->getRequestStack()->request->get("event") == "deleteAll" && $this->utilityPrivate->checkToken() == true) {
                $rolesDatabase = $this->rolesDatabase("deleteAll", null);

                if ($rolesDatabase == true) {
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/roles_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));

                    $this->response['render'] = $render;

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("roleController_7");
                }
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("roleController_8");
            
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
        $roleEntity = $this->entityManager->getRepository("UebusaitoBundle:Role")->find($id);
        
        if ($roleEntity != null) {
            // Create form
            $roleFormType = new RoleFormType();
            $formRoleProfile = $this->createForm($roleFormType, $roleEntity, Array(
                'validation_groups' => Array(
                    'role_profile'
                )
            ));
            
            $render = $this->renderView("UebusaitoBundle::render/control_panel/role_profile.html.twig", Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $roleEntity->getId(),
                'response' => $this->response,
                'form' => $formRoleProfile->createView()
            ));

            $this->response['render'] = $render;
        }
        else
            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("roleController_3");
    }
    
    private function listHtml($tableResult) {
        $listHtml = "";
        
        foreach ($tableResult as $key => $value) {
            $listHtml .= "<tr>
                <td class=\"id_column\">
                    {$value['id']}
                </td>
                <td class=\"checkbox_column\">
                    <input class=\"display_inline margin_clear\" type=\"checkbox\"/>
                </td>
                <td>
                    {$value['level']}
                </td>";
                $listHtml .= "<td class=\"horizontal_center\">";
                    if ($value['id'] > 2)
                        $listHtml .= "<button class=\"cp_role_deletion btn btn-danger\"><i class=\"fa fa-remove\"></i></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function rolesDatabase($type, $id) {
        if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM users_roles
                                                                WHERE id > :idExclude
                                                                AND id = :id");
            
            $query->bindValue(":idExclude", 2);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM users_roles
                                                                WHERE id > :idExclude");
            
            $query->bindValue(":idExclude", 2);
            
            return $query->execute();
        }
    }
}