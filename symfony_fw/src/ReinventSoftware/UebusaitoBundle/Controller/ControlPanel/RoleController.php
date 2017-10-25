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
    private $uebusaitoUtility;
    private $query;
    private $ajax;
    private $tableAndPagination;
    
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
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Logic
        $roleEntity = new Role();
        
        $form = $this->createForm(RoleFormType::class, $roleEntity, Array(
            'validation_groups' => Array('role_creation')
        ));
        $form->handleRequest($request);
        
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
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->tableAndPagination = new TableAndPagination($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Logic
        $_SESSION['role_profile_id'] = 0;
        
        $userRoleRows = $this->query->selectAllUserRolesDatabase();
        
        $tableAndPagination = $this->tableAndPagination->request($userRoleRows, 20, "role", true, true);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['list'] = $this->createHtmlList($tableAndPagination['list']);
        
        $form = $this->createForm(RolesSelectionFormType::class, null, Array(
            'validation_groups' => Array('roles_selection'),
            'choicesId' => array_reverse(array_column($userRoleRows, "id", "level"), true)
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
    *   name = "cp_role_profile_result",
    *   path = "/cp_role_profile_result/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/role_profile.html.twig")
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
            else if (empty($request->get("form_roles_selection")['id']) == false)
                $id = $request->get("form_roles_selection")['id'];
            
            $roleEntity = $this->entityManager->getRepository("UebusaitoBundle:Role")->find($id);
            
            if ($roleEntity != null) {
                $_SESSION['role_profile_id'] = $id;
                
                $form = $this->createForm(RoleFormType::class, $roleEntity, Array(
                    'validation_groups' => Array('role_profile')
                ));
                $form->handleRequest($request);
                
                $this->response['values']['id'] = $_SESSION['role_profile_id'];
                
                $this->response['render'] = $this->renderView("@UebusaitoBundleViews/render/control_panel/role_profile.html.twig", Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response,
                    'form' => $form->createView()
                ));
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("roleController_3");
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
    *   name = "cp_role_profile_save",
    *   path = "/cp_role_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/role_profile.html.twig")
    */
    public function profileSaveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Logic
        $roleEntity = $this->entityManager->getRepository("UebusaitoBundle:Role")->find($_SESSION['role_profile_id']);
        
        $form = $this->createForm(RoleFormType::class, $roleEntity, Array(
            'validation_groups' => Array('role_profile')
        ));
        $form->handleRequest($request);
        
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
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        // Logic
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($request->get("event") == "delete" && $this->utility->checkToken($request) == true) {
                $id = $request->get("id") == null ? $_SESSION['role_profile_id'] : $request->get("id");
                
                $rolesDatabase = $this->rolesDatabase("delete", $id);

                if ($rolesDatabase == true) {
                    $this->response['values']['id'] = $id;
                    
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("roleController_6");
                }
            }
            else if ($request->get("event") == "deleteAll" && $this->utility->checkToken($request) == true) {
                $rolesDatabase = $this->rolesDatabase("deleteAll", null);

                if ($rolesDatabase == true)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("roleController_7");
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
    private function createHtmlList($tableResult) {
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
                        $listHtml .= "<button class=\"cp_role_deletion button_custom_danger\"><i class=\"fa fa-remove\"></i></button>
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