<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Entity\MicroserviceDeploy;

use App\Form\MicroserviceDeployFormType;
use App\Form\MicroserviceDeploySelectFormType;

class MicroserviceDeployController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    private $tableAndPagination;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_microservice_deploy_render",
    *   path = "/cp_microservice_deploy_render/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"GET", "POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser()->getRoleUserId());
        
        // Logic
        $microserviceDeployRows = $this->query->selectAllMicroserviceDeployDatabase();

        $form = $this->createForm(MicroserviceDeploySelectFormType::class, null, Array(
            'validation_groups' => Array('microservice_deploy_render'),
            'choicesId' => array_reverse(array_column($microserviceDeployRows, "id", "name"), true)
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isValid() == true)
                $this->response['values']['renderHtml'] = $this->createRenderHtml($microserviceDeployRows);
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_1");
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
    *   name = "cp_microservice_deploy_create",
    *   path = "/cp_microservice_deploy_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy_create.html.twig")
    */
    public function createAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $microserviceDeployEntity = new MicroserviceDeploy();
        
        $_SESSION['microserviceDeployProfileId'] = 0;
        
        $form = $this->createForm(MicroserviceDeployFormType::class, $microserviceDeployEntity, Array(
            'validation_groups' => Array('microservice_deploy_create')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isValid() == true) {
                $microserviceDeployEntity->setActive(true);
                
                $shellScript = $this->shellScript($form);
                
                if ($shellScript == true) {
                    // Insert in database
                    $this->entityManager->persist($microserviceDeployEntity);
                    $this->entityManager->flush();
                    
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_2");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_3");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_3");
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
    *   name = "cp_microservice_deploy_select",
    *   path = "/cp_microservice_deploy_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy_select.html.twig")
    */
    public function selectAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->tableAndPagination = new TableAndPagination($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $_SESSION['microserviceDeployProfileId'] = 0;
        
        $microserviceDeployRows = $this->query->selectAllMicroserviceDeployDatabase();
        
        $tableAndPagination = $this->tableAndPagination->request($microserviceDeployRows, 20, "microserviceDeploy", true, true);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(MicroserviceDeploySelectFormType::class, null, Array(
            'validation_groups' => Array('microservice_deploy_select'),
            'choicesId' => array_reverse(array_column($microserviceDeployRows, "id", "name"), true)
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
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
    *   name = "cp_microservice_deploy_profile",
    *   path = "/cp_microservice_deploy_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy_profile.html.twig")
    */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if (($this->isCsrfTokenValid("intention", $request->get("token")) == true
                    || $this->isCsrfTokenValid("intention", $request->get("form_microservice_deploy_select")['_token']) == true)) {
                $id = 0;

                if (empty($request->get("id")) == false)
                    $id = $request->get("id");
                else if (empty($request->get("form_microservice_deploy_select")['id']) == false)
                    $id = $request->get("form_microservice_deploy_select")['id'];

                $microserviceDeployEntity = $this->entityManager->getRepository("App\Entity\MicroserviceDeploy")->find($id);

                if ($microserviceDeployEntity != null) {
                    $_SESSION['microserviceDeployProfileId'] = $id;

                    $form = $this->createForm(MicroserviceDeployFormType::class, $microserviceDeployEntity, Array(
                        'validation_groups' => Array('microservice_deploy_profile')
                    ));
                    $form->handleRequest($request);
                    
                    $this->response['values']['id'] = $_SESSION['microserviceDeployProfileId'];

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/microservice_deploy_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $form->createView()
                    ));
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_4");
            }
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
    *   name = "cp_microservice_deploy_profile_save",
    *   path = "/cp_microservice_deploy_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy_profile.html.twig")
    */
    public function profileSaveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $microserviceDeployEntity = $this->entityManager->getRepository("App\Entity\MicroserviceDeploy")->find($_SESSION['microserviceDeployProfileId']);
        
        $form = $this->createForm(MicroserviceDeployFormType::class, $microserviceDeployEntity, Array(
            'validation_groups' => Array('microservice_deploy_profile')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isValid() == true) {
                $shellScript = $this->shellScript($form);
                
                if ($shellScript == true) {
                    // Update in database
                    $this->entityManager->persist($microserviceDeployEntity);
                    $this->entityManager->flush();
                    
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_5");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_6");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_6");
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
    *   name = "cp_microservice_deploy_execute",
    *   path = "/cp_microservice_deploy_execute/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function executeAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $microserviceDeployRow = $this->query->selectMicroserviceDeployDatabase($_SESSION['microserviceDeployProfileId']);
                
                //$shellExec = shell_exec("");
                
                if ($shellExec == true)
                    $this->response['messages']['success'] = "Command executed.";
                else
                    $this->response['messages']['error'] = "Command not executed!";
            }
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
    *   name = "cp_microservice_deploy_delete",
    *   path = "/cp_microservice_deploy_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy_delete.html.twig")
    */
    public function deleteAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $_SESSION['microserviceDeployProfileId'] : $request->get("id");

                    $microserviceDeployDatabase = $this->microserviceDeployDatabase("delete", $id);

                    if ($microserviceDeployDatabase == true) {
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_7");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $microserviceDeployDatabase = $this->microserviceDeployDatabase("deleteAll", null);

                    if ($microserviceDeployDatabase == true)
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_8");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_9");

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
            'response' => $this->response
        );
    }
    
    // Functions private
    private function createRenderHtml($elements) {
        $renderHtml = "<ul class=\"mdc-list mdc-list--two-line mdc-list--avatar-list\">
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_1")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['name']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_2")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['description']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_3")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['git_user_email']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_4")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['git_user_name']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_5")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['git_clone_url']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_6")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['git_clone_path']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_7")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['user_git_script']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_8")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['user_web_script']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_9")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['root_web_path']}</span>
                </span>
            </li>
        </ul>
        <button id=\"microservice_deploy_start\" class=\"mdc-button mdc-button--dense mdc-button--raised\" type=\"submit\">{$this->utility->getTranslator()->trans("microserviceDeployController_10")}</button>";
        
        return $renderHtml;
    }
    
    private function createListHtml($elements) {
        $listHtml = "";
        
        foreach ($elements as $key => $value) {
            $listHtml .= "<tr>
                <td class=\"id_column\">
                    {$value['id']}
                </td>
                <td class=\"checkbox_column\">
                    <div class=\"mdc-checkbox\">
                        <input class=\"mdc-checkbox__native-control\" type=\"checkbox\"/>
                        <div class=\"mdc-checkbox__background\">
                            <svg class=\"mdc-checkbox__checkmark\" viewBox=\"0 0 24 24\">
                                <path class=\"mdc-checkbox__checkmark-path\" fill=\"none\" stroke=\"white\" d=\"M1.73,12.91 8.1,19.28 22.79,4.59\"/>
                            </svg>
                            <div class=\"mdc-checkbox__mixedmark\"></div>
                        </div>
                    </div>
                </td>
                <td>
                    {$value['name']}
                </td>
                <td>
                    {$value['description']}
                </td>
                <td>";
                    if ($value['active'] == 0)
                        $listHtml .= $this->utility->getTranslator()->trans("microserviceDeployController_11");
                    else
                        $listHtml .= $this->utility->getTranslator()->trans("microserviceDeployController_12");
                $listHtml .= "</td>
                <td class=\"horizontal_center\">
                    <button class=\"mdc-fab mdc-fab--mini cp_module_delete\" type=\"button\" aria-label=\"Delete\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function microserviceDeployDatabase($type, $id) {
        if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM microservice_deploy
                                                                WHERE id = :id");
            
            $query->bindValue(":id", $id);

            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM microservice_deploy");

            return $query->execute();
        }
    }
    
    private function shellScript($form) {
        /*shellScript = "#!/bin/bash

        gitCloneUrl=https://rubygroupe-git:ruby2015@github.com/RUBYGROUPE/apolis.git
        gitClonePath=/home/user_1/www/apolis_dev
        userGitScript=user_1
        userWebScript=user_1:www-data
        rootWebPath=/home/user_1/www/apolis_dev/src

        #git config --global core.mergeoptions --no-edit
        git config --global user.email \"ruby-devops@rubygroupe.jp\"
        git config --global user.name \"rubygroupe-git\"

        echo \"Git shell\"
        read -p \"1) Clone - 2) Pull: - 3) Reset: > \" gitChoice

        if [ ! -z $gitChoice ]
        then
                if [ $gitChoice -eq 1 ]
                then
                        sudo -u $userGitScript git clone $gitCloneUrl $gitClonePath
                elif [ $gitChoice -eq 2 ]
                then
                        read -p \"Insert branch name: > \" branchNameA branchNameB

                        if [ ! -z \"$branchNameA $branchNameB\" ]
                        then
                                cd $gitClonePath
                                sudo -u $userGitScript git pull $gitCloneUrl $branchNameA $branchNameB
                        else
                                echo \"Empty value, please restart!\"
                        fi
                elif [ $gitChoice -eq 3 ]
                then
                        read -p \"Insert branch name: > \" branchNameA branchNameB

                        if [ ! -z \"$branchNameA $branchNameB\" ]
                        then
                                cd $gitClonePath
                                sudo -u $userGitScript git fetch --all
                                sudo -u $userGitScript git reset --hard $branchNameA $branchNameB
                        fi
                fi
                echo \"Settings project in progress, please wait...\"

                sudo chown -R $userWebScript $rootWebPath
                sudo find $rootWebPath -type d -exec chmod 775 {} \;
                sudo find $rootWebPath -type f -exec chmod 664 {} \;

                echo \"Finito ciao ciao =D\"
        else
                echo \"Empty value, please restart!\"
        fi";*/
        
        $shellScript = "#!/bin/bash
        echo \"Git shell\"
        ";
        
        $pathKey = $this->utility->getPathSrc() . "/Controller/Microservice/Deploy/amazon_server.pem";
        $pathSh = $this->utility->getPathSrc() . "/Controller/Microservice/Deploy/connection.sh";
        //chmod($pathSh, 0664);
        //chmod($pathSh, 0111);
        
        /*$path = $this->utility->getPathSrc() . "/Controller/Microservice/Deploy/" . $form->get("name")->getData() . ".sh";
        
        chmod($path, 0664);
        $content = file_put_contents($path, $shellScript);
        chmod($path, 0111);*/
        
        /*$resFile = fopen("ssh2.sftp://18.213.67.135/".$csv_filename, 'w');
        $srcFile = fopen("/home/myusername/".$csv_filename, 'r');
        $writtenBytes = stream_copy_to_stream($srcFile, $resFile);
        fclose($resFile);
        fclose($srcFile);*/
        
        //ssh -i /home/user_1/www/key.pem user@18.213.67.135 "ls"
        //$sshCmd = "ssh user_batch@18.213.67.135; Apolis_dev_batch_dda814; 'bash -s' < \"/path/to/script.sh\"; logout;";
        
        //$shellExec = shell_exec("/usr/bin/ssh -i $pathKey ubuntu@18.213.67.135");
        $shellExec = shell_exec("sh /home/user_1/www/ls1.reinventsoftware.org/uebusaito/symfony_fw/src/Controller/Microservice/Deploy/connection.sh 2>&1");
        var_dump($shellExec);
        return $shellExec;
    }
}