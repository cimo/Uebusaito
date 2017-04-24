<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Table;

use ReinventSoftware\UebusaitoBundle\Entity\User;

use ReinventSoftware\UebusaitoBundle\Form\UserFormType;

use ReinventSoftware\UebusaitoBundle\Form\UsersSelectionFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\UsersSelectionModel;

class UserController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $utility;
    private $utilityPrivate;
    private $query;
    private $ajax;
    private $table;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:control_panel/user_creation.html.twig")
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
        
        $userEntity = new User();
        $userEntity->setRoleId("1,");
        
        // Create form
        $userFormType = new UserFormType();
        $form = $this->createForm($userFormType, $userEntity, Array(
            'validation_groups' => Array(
                'user_creation'
            )
        ));
        $form->setData($userEntity);
        
        $this->response['values']['rolesSelect'] = $this->utilityPrivate->createRolesSelectHtml("form_user_roleId_field", true);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->utility->getRequestStack());

                // Check form
                if ($form->isValid() == true) {
                    $message = $this->utilityPrivate->configureUserProfilePassword("withoutOld", $userEntity, $form);

                    if ($message == "ok") {
                        $this->utilityPrivate->configureUserParameters($userEntity);
                        
                        mkdir("{$this->utility->getPathSrcBundle()}/Resources/files/{$form->get("username")->getData()}");
                        mkdir("{$this->utility->getPathSrcBundle()}/Resources/public/images/users/{$form->get("username")->getData()}");
                        mkdir("{$this->utility->getPathWebBundle()}/images/users/{$form->get("username")->getData()}");

                        // Insert in database
                        $this->entityManager->persist($userEntity);
                        $this->entityManager->flush();
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("userController_1");
                    }
                    else
                        $this->response['messages']['error'] = $message;
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("userController_2");
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
     * @Template("UebusaitoBundle:render:control_panel/users_selection.html.twig")
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
        $usersSelectionFormType = new UsersSelectionFormType($this->container, $this->entityManager);
        $form = $this->createForm($usersSelectionFormType, new UsersSelectionModel(), Array(
            'validation_groups' => Array(
                'users_selection'
            )
        ));
        
        // Pagination
        $userRows = $this->query->selectAllUsersDatabase(1);
        
        $tableResult = $this->table->request($userRows, 20, "user", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml($userRows, $tableResult['list']);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $id = 0;
            
            $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
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
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/users_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));

                    $this->response['render'] = $render;
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("userController_3");
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
     * @Template("UebusaitoBundle:render:control_panel/user_profile.html.twig")
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
        
        $userEntity = $this->entityManager->getRepository("UebusaitoBundle:User")->find($this->urlExtra);
        
        $usernameOld = $userEntity->getUsername();
        
        // Create form
        $userFormType = new UserFormType();
        $form = $this->createForm($userFormType, $userEntity, Array(
            'validation_groups' => Array(
                'user_profile'
            )
        ));
        
        $this->response['values']['rolesSelect'] = $this->utilityPrivate->createRolesSelectHtml("form_user_roleId_field", true);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->utility->getRequestStack());

                // Check form
                if ($form->isValid() == true) {
                    $message = $this->utilityPrivate->configureUserProfilePassword("withoutOld", $userEntity, $form);

                    if ($message == "ok") {
                        if (file_exists("{$this->utility->getPathSrcBundle()}/Resources/files/$usernameOld") == true)
                            rename("{$this->utility->getPathSrcBundle()}/Resources/files/$usernameOld",
                                    "{$this->utility->getPathSrcBundle()}/Resources/files/{$form->get("username")->getData()}");
                        
                        if (file_exists("{$this->utility->getPathSrcBundle()}/Resources/public/images/users/$usernameOld") == true)
                            rename("{$this->utility->getPathSrcBundle()}/Resources/public/images/users/$usernameOld",
                                    "{$this->utility->getPathSrcBundle()}/Resources/public/images/users/{$form->get("username")->getData()}");
                        
                        if (file_exists("{$this->utility->getPathWebBundle()}/images/users/$usernameOld") == true)
                            rename("{$this->utility->getPathWebBundle()}/images/users/$usernameOld",
                                    "{$this->utility->getPathWebBundle()}/images/users/{$form->get("username")->getData()}");
                        
                        if ($form->get("notLocked")->getData() == true)
                            $userEntity->setHelpCode("");
                        
                        // Update in database
                        $this->entityManager->persist($userEntity);
                        $this->entityManager->flush();

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("userController_4");
                    }
                    else
                        $this->response['messages']['error'] = $message;
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("userController_5");
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
     * @Template("UebusaitoBundle:render:control_panel/user_deletion.html.twig")
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
        $userRows = $this->query->selectAllUsersDatabase(1);
        
        $tableResult = $this->table->request($userRows, 20, "user", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml($userRows, $tableResult['list']);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && $chekRoleLevel == true) {
            $sessionActivity = $this->utilityPrivate->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                if ($this->utility->getRequestStack()->request->get("event") == "delete" && $this->utilityPrivate->checkToken() == true) {
                    $userEntity = $this->entityManager->getRepository("UebusaitoBundle:User")->find($this->utility->getRequestStack()->request->get("id"));
                    
                    $this->utility->removeDirRecursive("{$this->utility->getPathSrcBundle()}/Resources/files/{$userEntity->getUsername()}", true);
                    $this->utility->removeDirRecursive("{$this->utility->getPathSrcBundle()}/Resources/public/images/users/{$userEntity->getUsername()}", true);
                    $this->utility->removeDirRecursive("{$this->utility->getPathWebBundle()}/images/users/{$userEntity->getUsername()}", true);

                    $usersDatabase = $this->usersDatabase("delete", $userEntity->getId());
                    
                    if ($usersDatabase == true)
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("userController_6");
                }
                else if ($this->utility->getRequestStack()->request->get("event") == "deleteAll" && $this->utilityPrivate->checkToken() == true) {
                    $userRows = $this->query->selectAllUsersDatabase(1);

                    for ($i = 0; $i < count($userRows); $i ++) {
                        $this->utility->removeDirRecursive("{$this->utility->getPathSrcBundle()}/Resources/files/{$userRows[$i]['username']}", true);
                        $this->utility->removeDirRecursive("{$this->utility->getPathSrcBundle()}/Resources/public/images/users/{$userRows[$i]['username']}", true);
                        $this->utility->removeDirRecursive("{$this->utility->getPathWebBundle()}/images/users/{$userRows[$i]['username']}", true);
                    }

                    $usersDatabase = $this->usersDatabase("deleteAll");
                    
                    if ($usersDatabase == true) {
                        $render = $this->renderView("UebusaitoBundle::render/control_panel/users_selection_desktop.html.twig", Array(
                            'urlLocale' => $this->urlLocale,
                            'urlCurrentPageId' => $this->urlCurrentPageId,
                            'urlExtra' => $this->urlExtra,
                            'response' => $this->response
                        ));
                        
                        $this->response['render'] = $render;
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("userController_7");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("userController_8");
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
        $userEntity = $this->entityManager->getRepository("UebusaitoBundle:User")->find($id);
        
        if ($userEntity != null) {
            // Create form
            $userFormType = new UserFormType();
            $formUserProfile = $this->createForm($userFormType, $userEntity, Array(
                'validation_groups' => Array(
                    'user_profile'
                )
            ));
            
            $this->response['values']['rolesSelect'] = $this->utilityPrivate->createRolesSelectHtml("form_user_roleId_field", true);
            
            $render = $this->renderView("UebusaitoBundle::render/control_panel/user_profile.html.twig", Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $userEntity->getId(),
                'response' => $this->response,
                'form' => $formUserProfile->createView()
            ));

            $this->response['render'] = $render;
        }
        else
            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("userController_3");
    }
    
    private function listHtml($userRows, $tableResult) {
        $listHtml = "";
        
        $roleLevel = Array();
        foreach ($userRows as $key => $value)
            $roleLevel[] = $this->query->selectUserRoleLevelDatabase($value['role_id'], true);
        
        foreach ($tableResult as $key => $value) {
            $listHtml .= "<tr>
                <td class=\"id_column\">
                    {$value['id']}
                </td>
                <td class=\"checkbox_column\">
                    <input class=\"display_inline margin_clear\" type=\"checkbox\"/>
                </td>
                <td>
                    {$value['username']}
                </td>
                <td>
                    {$roleLevel[$key][0]}
                </td>
                <td>
                    {$value['name']}
                </td>
                <td>
                    {$value['surname']}
                </td>
                <td>
                    {$value['email']}
                </td>
                <td>
                    {$value['company_name']}
                </td>
                <td>";
                    if ($value['not_locked'] == 0)
                        $listHtml .= $this->utility->getTranslator()->trans("userController_9");
                    else
                        $listHtml .= $this->utility->getTranslator()->trans("userController_10");
                $listHtml .= "</td>
                <td class=\"horizontal_center\">
                    <button class=\"cp_user_deletion btn btn-danger\"><i class=\"fa fa-remove\"></i></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function usersDatabase($type, $id = null) {
        if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM users
                                                                WHERE id > :idExclude
                                                                AND id = :id");
            
            $query->bindValue(":idExclude", 1);
            $query->bindValue(":id", $id);

            $query->execute();
            
            // Payments
            $query = $this->utility->getConnection()->prepare("DELETE FROM payments
                                                                WHERE user_id > :idExclude
                                                                AND user_id = :id");
            
            $query->bindValue(":idExclude", 1);
            $query->bindValue(":id", $id);

            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM users
                                                                WHERE id > :idExclude");

            $query->bindValue(":idExclude", 1);

            $query->execute();
            
            // Payments
            $query = $this->utility->getConnection()->prepare("DELETE FROM payments
                                                                WHERE user_id > :idExclude");
            
            $query->bindValue(":idExclude", 1);

            return $query->execute();
        }
    }
}