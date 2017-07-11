<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

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

use ReinventSoftware\UebusaitoBundle\Entity\Role;

use ReinventSoftware\UebusaitoBundle\Form\RoleFormType;
use ReinventSoftware\UebusaitoBundle\Form\RolesSelectionFormType;

class RoleController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    private $table;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_role_creation",
    *   path = "/cp_role_creation/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/role_creation.html.twig")
    */
    public function creationAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        $roleEntity = new Role();
        
        // Form
        $form = $this->createForm(RoleFormType::class, $roleEntity, Array(
            'validation_groups' => Array('role_creation')
        ));
        $form->handleRequest($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
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
    * @Route(
    *   name = "cp_roles_selection",
    *   path = "/cp_roles_selection/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/roles_selection.html.twig")
    */
    public function selectionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        $userRoleRows = $this->query->selectAllUserRolesDatabase();
        
        $tableResult = $this->table->request($userRoleRows, 20, "role", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->createListHtml($tableResult['list']);
        
        // Form
        $form = $this->createForm(RolesSelectionFormType::class, null, Array(
            'validation_groups' => Array('roles_selection'),
            'choicesId' => array_reverse(array_column($this->query->selectAllUserRolesDatabase(), "id", "level"), true)
        ));
        $form->handleRequest($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            $id = 0;
            
            if ($form->isValid() == true) {
                $id = $form->get("id")->getData();

                $this->selectionResult($id, $request);
            }
            else if ($request->get("event") == null && $this->utilityPrivate->checkToken($request) == true) {
                $id = $request->get("id") == "" ? 0 : $request->get("id");

                $this->selectionResult($id, $request);
            }
            else if (($request->get("event") == "refresh" && $this->utilityPrivate->checkToken($request) == true) || $this->table->checkPost() == true) {
                $render = $this->renderView("@UebusaitoBundleViews/render/control_panel/roles_selection_desktop.html.twig", Array(
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
    * @Route(
    *   name = "cp_role_profile",
    *   path = "/cp_role_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/role_profile.html.twig")
    */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        $roleEntity = $this->entityManager->getRepository("UebusaitoBundle:Role")->find($this->urlExtra);
        
        // Form
        $form = $this->createForm(RoleFormType::class, $roleEntity, Array(
            'validation_groups' => Array('role_profile')
        ));
        $form->handleRequest($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
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
    * @Route(
    *   name = "cp_role_deletion",
    *   path = "/cp_role_deletion/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/role_deletion.html.twig")
    */
    public function deletionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        $userRoleRows = $this->query->selectAllUserRolesDatabase();
        
        $tableResult = $this->table->request($userRoleRows, 20, "role", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->createListHtml($tableResult['list']);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($request->get("event") == "delete" && $this->utilityPrivate->checkToken($request) == true) {
                $rolesDatabase = $this->rolesDatabase("delete", $request->get("id"));

                if ($rolesDatabase == true)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("roleController_6");
            }
            else if ($request->get("event") == "deleteAll" && $this->utilityPrivate->checkToken($request) == true) {
                $rolesDatabase = $this->rolesDatabase("deleteAll", null);

                if ($rolesDatabase == true) {
                    $render = $this->renderView("@UebusaitoBundleViews/render/control_panel/roles_selection_desktop.html.twig", Array(
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
    private function selectionResult($id, $request) {
        $roleEntity = $this->entityManager->getRepository("UebusaitoBundle:Role")->find($id);
        
        if ($roleEntity != null) {
            // Form
            $form = $this->createForm(RoleFormType::class, $roleEntity, Array(
                'validation_groups' => Array('role_profile')
            ));
            $form->handleRequest($request);
            
            $render = $this->renderView("@UebusaitoBundleViews/render/control_panel/role_profile.html.twig", Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $roleEntity->getId(),
                'response' => $this->response,
                'form' => $form->createView()
            ));

            $this->response['render'] = $render;
        }
        else
            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("roleController_3");
    }
    
    private function createListHtml($tableResult) {
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