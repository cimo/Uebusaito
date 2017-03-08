<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Entity\User;

use ReinventSoftware\UebusaitoBundle\Form\UserFormType;

use ReinventSoftware\UebusaitoBundle\Form\UsersSelectionFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\UsersSelectionModel;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Table;

class UserController extends Controller {
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
     * @Template("UebusaitoBundle:render:control_panel/user_creation.html.twig")
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
        
        $user = new User();
        $user->setRoleId("1,");
        
        // Create form
        $userFormType = new UserFormType($this->utility);
        $form = $this->createForm($userFormType, $user, Array(
            'validation_groups' => Array(
                'user_creation'
            )
        ));
        $form->setData($user);
        
        $this->response['values']['rolesSelect'] = $this->utility->createRolesSelectHtml("form_user_roleId_field", "required=\"required\"");
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);

                // Check form
                if ($form->isValid() == true) {
                    $message = $this->utility->configureUserProfilePassword($user, 2, $form);

                    if ($message == "ok") {
                        $this->utility->configureUserParameters($user);

                        // Insert in database
                        $this->entityManager->persist($user);
                        $this->entityManager->flush();
                        
                        mkdir("{$this->utility->getPathRootFull()}/src/ReinventSoftware/UebusaitoBundle/Resources/files/{$form->get("username")->getData()}");

                        $this->response['messages']['success'] = $this->translator->trans("userController_1");
                    }
                    else
                        $this->response['messages']['error'] = $message;
                }
                else {
                    $this->response['messages']['error'] = $this->translator->trans("userController_2");
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        $this->table = new Table($this->utility);
        
        $this->listHtml = "";
        
        $this->response = Array();
        
        // Create form
        $usersSelectionFormType = new UsersSelectionFormType($this->utility);
        $form = $this->createForm($usersSelectionFormType, new UsersSelectionModel(), Array(
            'validation_groups' => Array(
                'users_selection'
            )
        ));
        
        // Pagination
        $userRows = $this->utility->getQuery()->selectAllUsersFromDatabase(1);
        
        $tableResult = $this->table->request($userRows, 20, "user", true, true);
        
        $this->listHtml($userRows, $tableResult['list']);
        
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
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/users_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));

                    $this->response['render'] = $render;
                }
                else {
                    $this->response['messages']['error'] = $this->translator->trans("userController_3");
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        $user = $this->entityManager->getRepository("UebusaitoBundle:User")->find($this->urlExtra);
        
        $usernameOld = $user->getUsername();
        
        // Create form
        $userFormType = new UserFormType($this->utility);
        $form = $this->createForm($userFormType, $user, Array(
            'validation_groups' => Array(
                'user_profile'
            )
        ));
        
        $this->response['values']['rolesSelect'] = $this->utility->createRolesSelectHtml("form_user_roleId_field", "required=\"required\"");
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);

                // Check form
                if ($form->isValid() == true) {
                    $message = $this->utility->configureUserProfilePassword($user, 2, $form);

                    if ($message == "ok") {
                        rename("{$this->utility->getPathRootFull()}/src/ReinventSoftware/UebusaitoBundle/Resources/files/$usernameOld",
                                "{$this->utility->getPathRootFull()}/src/ReinventSoftware/UebusaitoBundle/Resources/files/{$form->get("username")->getData()}");
                        
                        if ($form->get("notLocked")->getData() == true)
                            $user->setHelpCode("");
                        
                        // Insert in database
                        $this->entityManager->persist($user);
                        $this->entityManager->flush();

                        $this->response['messages']['success'] = $this->translator->trans("userController_4");
                    }
                    else
                        $this->response['messages']['error'] = $message;
                }
                else {
                    $this->response['messages']['error'] = $this->translator->trans("userController_5");
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        $this->table = new Table($this->utility);
        
        $this->listHtml = "";
        
        $this->response = Array();
        
        $userRows = $this->utility->getQuery()->selectAllUsersFromDatabase(1);
        
        $tableResult = $this->table->request($userRows, 20, "user", true, true);
        
        $this->listHtml($userRows, $tableResult['list']);
        
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
                    if ($this->requestStack->request->get("id") > 1) {
                        $user = $this->entityManager->getRepository("UebusaitoBundle:User")->find($this->requestStack->request->get("id"));

                        // Remove from database
                        $this->entityManager->remove($user);
                        $this->entityManager->flush();

                        $this->utility->removeDirRecursive("{$this->utility->getPathRootFull()}/src/ReinventSoftware/UebusaitoBundle/Resources/files/{$user->getUsername()}", true);
                    }

                    $this->response['messages']['success'] = $this->translator->trans("userController_6");
                }
                else if (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $this->requestStack->request->get("event") == "deleteAll") {
                    // Remove from database
                    $this->usersInDatabase();
                    
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/users_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                    
                    $this->response['render'] = $render;

                    $this->response['messages']['success'] = $this->translator->trans("userController_7");
                }
                else
                    $this->response['messages']['error'] = $this->translator->trans("userController_8");
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
        $user = $this->entityManager->getRepository("UebusaitoBundle:User")->find($id);
        
        if ($user != null) {
            // Create form
            $userFormType = new UserFormType($this->utility);
            $formUserProfile = $this->createForm($userFormType, $user, Array(
                'validation_groups' => Array(
                    'user_profile'
                )
            ));
            
            $this->response['values']['rolesSelect'] = $this->utility->createRolesSelectHtml("form_user_roleId_field", "required=\"required\"");
            
            $render = $this->renderView("UebusaitoBundle::render/control_panel/user_profile.html.twig", Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $user->getId(),
                'response' => $this->response,
                'form' => $formUserProfile->createView()
            ));

            $this->response['render'] = $render;
        }
        else
            $this->response['messages']['error'] = $this->translator->trans("userController_3");
    }
    
    private function listHtml($userRows, $tableResult) {
        $roleLevel = Array();
        foreach ($userRows as $key => $value)
            $roleLevel[] = $this->utility->getQuery()->selectUserRoleLevelFromDatabase($value['role_id'], true);
        
        foreach ($tableResult as $key => $value) {
            $this->listHtml .= "<tr>
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
                        $this->listHtml .= $this->translator->trans("userController_9");
                    else
                        $this->listHtml .= $this->translator->trans("userController_10");
                $this->listHtml .= "</td>
                <td class=\"horizontal_center\">
                    <button class=\"cp_user_deletion btn btn-danger\"><i class=\"fa fa-remove\"></i></button>
                </td>
            </tr>";
        }
    }
    
    private function usersInDatabase() {
        $connection = $this->entityManager->getConnection();
        
        $query = $connection->prepare("DELETE FROM users
                                        WHERE id > :idExclude");
        
        $query->bindValue(":idExclude", "1");

        $query->execute();
    }
}