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

use ReinventSoftware\UebusaitoBundle\Entity\User;

use ReinventSoftware\UebusaitoBundle\Form\UserFormType;
use ReinventSoftware\UebusaitoBundle\Form\UsersSelectionFormType;

class UserController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $utilityPrivate;
    private $query;
    private $ajax;
    private $table;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_user_creation",
    *   path = "/cp_user_creation/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/user_creation.html.twig")
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
        
        $this->response['values']['rolesSelect'] = $this->utilityPrivate->createRolesSelectHtml("form_user_roleId_field", true);
        
        $userEntity = new User();
        $userEntity->setRoleId("1,");
        
        // Form
        $form = $this->createForm(UserFormType::class, $userEntity, Array(
            'validation_groups' => Array('user_creation')
        ));
        $form->setData($userEntity);
        $form->handleRequest($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($form->isValid() == true) {
                $message = $this->utilityPrivate->assigUserPassword("withoutOld", $userEntity, $form);

                if ($message == "ok") {
                    $this->utilityPrivate->configureUserParameters($userEntity);

                    mkdir("{$this->utility->getPathSrcBundle()}/Resources/files/{$form->get("username")->getData()}");
                    mkdir("{$this->utility->getPathSrcBundle()}/Resources/public/files/{$form->get("username")->getData()}");
                    mkdir("{$this->utility->getPathWebBundle()}/files/{$form->get("username")->getData()}");

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
    *   name = "cp_users_selection",
    *   path = "/cp_users_selection/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/users_selection.html.twig")
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
        $userRows = $this->query->selectAllUsersDatabase(1);
        
        $tableResult = $this->table->request($userRows, 20, "user", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml($userRows, $tableResult['list']);
        
        // Form
        $form = $this->createForm(UsersSelectionFormType::class, null, Array(
            'validation_groups' => Array('users_selection'),
            'choicesId' => array_reverse(array_column($this->query->selectAllUsersDatabase(1), "id", "username"), true)
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
            else if (($request->get("event") == "refresh" && $this->utilityPrivate->checkToken($request) == true) ||
                        $this->table->checkPost() == true) {
                $render = $this->renderView("@UebusaitoBundleViews/render/control_panel/users_selection_desktop.html.twig", Array(
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
    *   name = "cp_user_profile",
    *   path = "/cp_user_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/user_profile.html.twig")
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
        
        $this->response['values']['rolesSelect'] = $this->utilityPrivate->createRolesSelectHtml("form_user_roleId_field", true);
        
        // Form
        $form = $this->createForm(UserFormType::class, null, Array(
            'validation_groups' => Array('user_profile')
        ));
        $form->handleRequest($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($form->isValid() == true) {
                $userEntity = $this->entityManager->getRepository("UebusaitoBundle:User")->find($this->urlExtra);
                
                $message = $this->utilityPrivate->assigUserPassword("withoutOld", $userEntity, $form);

                if ($message == "ok") {
                    $usernameOld = $userEntity->getUsername();
                    
                    if (file_exists("{$this->utility->getPathSrcBundle()}/Resources/files/$usernameOld") == true)
                        @rename("{$this->utility->getPathSrcBundle()}/Resources/files/$usernameOld",
                                "{$this->utility->getPathSrcBundle()}/Resources/files/{$form->get("username")->getData()}");

                    if (file_exists("{$this->utility->getPathSrcBundle()}/Resources/public/files/$usernameOld") == true)
                        @rename("{$this->utility->getPathSrcBundle()}/Resources/public/files/$usernameOld",
                                "{$this->utility->getPathSrcBundle()}/Resources/public/files/{$form->get("username")->getData()}");

                    if (file_exists("{$this->utility->getPathWebBundle()}/files/$usernameOld") == true)
                        @rename("{$this->utility->getPathWebBundle()}/files/$usernameOld",
                                "{$this->utility->getPathWebBundle()}/files/{$form->get("username")->getData()}");

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
    *   name = "cp_user_deletion",
    *   path = "/cp_user_deletion/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/user_deletion.html.twig")
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
        $userRows = $this->query->selectAllUsersDatabase(1);
        
        $tableResult = $this->table->request($userRows, 20, "user", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml($userRows, $tableResult['list']);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($request->get("event") == "delete" && $this->utilityPrivate->checkToken($request) == true) {
                $userEntity = $this->entityManager->getRepository("UebusaitoBundle:User")->find($request->get("id"));

                $this->utility->removeDirRecursive("{$this->utility->getPathSrcBundle()}/Resources/files/{$userEntity->getUsername()}", true);
                $this->utility->removeDirRecursive("{$this->utility->getPathSrcBundle()}/Resources/public/files/{$userEntity->getUsername()}", true);
                $this->utility->removeDirRecursive("{$this->utility->getPathWebBundle()}/files/{$userEntity->getUsername()}", true);

                $usersDatabase = $this->usersDatabase("delete", $userEntity->getId());

                if ($usersDatabase == true)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("userController_6");
            }
            else if ($request->get("event") == "deleteAll" && $this->utilityPrivate->checkToken($request) == true) {
                $userRows = $this->query->selectAllUsersDatabase(1);

                for ($i = 0; $i < count($userRows); $i ++) {
                    $this->utility->removeDirRecursive("{$this->utility->getPathSrcBundle()}/Resources/files/{$userRows[$i]['username']}", true);
                    $this->utility->removeDirRecursive("{$this->utility->getPathSrcBundle()}/Resources/public/files/{$userRows[$i]['username']}", true);
                    $this->utility->removeDirRecursive("{$this->utility->getPathWebBundle()}/files/{$userRows[$i]['username']}", true);
                }

                $usersDatabase = $this->usersDatabase("deleteAll", null);

                if ($usersDatabase == true) {
                    $render = $this->renderView("@UebusaitoBundleViews/render/control_panel/users_selection_desktop.html.twig", Array(
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
        $userEntity = $this->entityManager->getRepository("UebusaitoBundle:User")->find($id);
        
        if ($userEntity != null) {
            $this->response['values']['rolesSelect'] = $this->utilityPrivate->createRolesSelectHtml("form_user_roleId_field", true);
            
            // Form
            $form = $this->createForm(UserFormType::class, $userEntity, Array(
                'validation_groups' => Array('user_profile')
            ));
            $form->handleRequest($request);
            
            $render = $this->renderView("@UebusaitoBundleViews/render/control_panel/user_profile.html.twig", Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $userEntity->getId(),
                'response' => $this->response,
                'form' => $form->createView()
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
    
    private function usersDatabase($type, $id) {
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