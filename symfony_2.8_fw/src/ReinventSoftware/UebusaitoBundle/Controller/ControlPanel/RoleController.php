<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Entity\Role;

use ReinventSoftware\UebusaitoBundle\Form\RoleFormType;

use ReinventSoftware\UebusaitoBundle\Form\RolesSelectionFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\RolesSelectionModel;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Table;

class RoleController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    private $requestStack;
    private $translator;
    
    private $utility;
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        $role = new Role();
        
        // Create form
        $roleFormType = new RoleFormType();
        $form = $this->createForm($roleFormType, $role, Array(
            'validation_groups' => Array(
                'role_creation'
            )
        ));
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);

                // Check form
                if ($form->isValid() == true) {
                    // Insert in database
                    $this->entityManager->persist($role);
                    $this->entityManager->flush();
                    
                    $this->response['messages']['success'] = $this->translator->trans("roleController_1");
                }
                else {
                    $this->response['messages']['error'] = $this->translator->trans("roleController_2");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        $this->table = new Table($this->utility);
        
        $this->listHtml = "";
        
        $this->response = Array();
        
        // Create form
        $rolesSelectionFormType = new RolesSelectionFormType($this->utility);
        $form = $this->createForm($rolesSelectionFormType, new RolesSelectionModel(), Array(
            'validation_groups' => Array(
                'roles_selection'
            )
        ));
        
        // Pagination
        $userRoleRows = $this->utility->getQuery()->selectAllUserRolesFromDatabase();
        
        $tableResult = $this->table->request($userRoleRows, 20, "role", true, true);
        
        $this->listHtml($tableResult['list']);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml;
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $id = 0;
            
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);
                
                // Check form
                if ($form->isValid() == true) {
                    $id = $form->get("id")->getData();
                    
                    $this->selectionResult($id);
                }
                else if (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $form->isValid() == false && $this->requestStack->request->get("event") == null) {
                    $id = $this->requestStack->request->get("id") == "" ? 0 : $this->requestStack->request->get("id");
                    
                    $this->selectionResult($id);
                }
                else if (isset($_POST['searchWritten']) == true && isset($_POST['paginationCurrent']) == true || (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $this->requestStack->request->get("event") == "refresh")) {
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/roles_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                    
                    $this->response['render'] = $render;
                }
                else {
                    $this->response['messages']['error'] = $this->translator->trans("roleController_3");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        $role = $this->entityManager->getRepository("UebusaitoBundle:Role")->find($this->urlExtra);
        
        // Create form
        $roleFormType = new RoleFormType();
        $form = $this->createForm($roleFormType, $role, Array(
            'validation_groups' => Array(
                'role_profile'
            )
        ));
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);

                // Check form
                if ($form->isValid() == true) {
                    // Insert in database
                    $this->entityManager->persist($role);
                    $this->entityManager->flush();
                    
                    $this->response['messages']['success'] = $this->translator->trans("roleController_4");
                }
                else {
                    $this->response['messages']['error'] = $this->translator->trans("roleController_5");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        $this->table = new Table($this->utility);
        
        $this->listHtml = "";
        
        $this->response = Array();
        
        // Pagination
        $userRoleRows = $this->utility->getQuery()->selectAllUserRolesFromDatabase();
        
        $tableResult = $this->table->request($userRoleRows, 20, "role", true, true);
        
        $this->listHtml($tableResult['list']);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml;
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                if (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $this->requestStack->request->get("event") == null) {
                    if ($this->requestStack->request->get("id") > 2) {
                        $role = $this->entityManager->getRepository("UebusaitoBundle:Role")->find($this->requestStack->request->get("id"));

                        // Remove from database
                        $this->entityManager->remove($role);
                        $this->entityManager->flush();
                    }
                    
                    $this->response['messages']['success'] = $this->translator->trans("roleController_6");
                }
                else if (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $this->requestStack->request->get("event") == "deleteAll") {
                    // Remove from database
                    $this->rolesInDatabase();
                    
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/roles_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                    
                    $this->response['render'] = $render;

                    $this->response['messages']['success'] = $this->translator->trans("roleController_7");
                }
                else
                    $this->response['messages']['error'] = $this->translator->trans("roleController_8");
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
            'response' => $this->response
        );
    }
    
    // Functions private
    private function selectionResult($id) {
        $role = $this->entityManager->getRepository("UebusaitoBundle:Role")->find($id);
        
        if ($role != null) {
            // Create form
            $roleFormType = new RoleFormType();
            $formRoleProfile = $this->createForm($roleFormType, $role, Array(
                'validation_groups' => Array(
                    'role_profile'
                )
            ));
            
            $render = $this->renderView("UebusaitoBundle::render/control_panel/role_profile.html.twig", Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $role->getId(),
                'response' => $this->response,
                'form' => $formRoleProfile->createView()
            ));

            $this->response['render'] = $render;
        }
        else
            $this->response['messages']['error'] = $this->translator->trans("roleController_3");
    }
    
    private function listHtml($tableResult) {
        foreach ($tableResult as $key => $value) {
            $this->listHtml .= "<tr>
                <td class=\"id_column\">
                    {$value['id']}
                </td>
                <td class=\"checkbox_column\">
                    <input class=\"display_inline margin_clear\" type=\"checkbox\"/>
                </td>
                <td>
                    {$value['level']}
                </td>";
                $this->listHtml .= "<td class=\"horizontal_center\">";
                    if ($value['id'] > 2)
                        $this->listHtml .= "<button class=\"cp_role_deletion btn btn-danger\"><i class=\"fa fa-remove\"></i></button>
                </td>
            </tr>";
        }
    }
    
    private function rolesInDatabase() {
        $connection = $this->entityManager->getConnection();
        
        $query = $connection->prepare("DELETE FROM users_roles
                                        WHERE id > :idExclude");

        $query->bindValue(":idExclude", 2);

        $query->execute();
    }
}