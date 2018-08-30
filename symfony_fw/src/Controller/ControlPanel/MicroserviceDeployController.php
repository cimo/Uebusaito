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

use App\Service\FileUploader;

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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser()->getRoleUserId());
        
        // Logic
        $microserviceDeployRows = $this->query->selectAllMicroserviceDeployDatabase();

        $form = $this->createForm(MicroserviceDeploySelectFormType::class, null, Array(
            'validation_groups' => Array('microservice_deploy_render'),
            'choicesId' => array_reverse(array_column($microserviceDeployRows, "id", "name"), true)
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true)
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser()->getRoleUserId());
        
        // Logic
        $microserviceDeployEntity = new MicroserviceDeploy();
        
        $_SESSION['microserviceDeployProfileId'] = 0;
        
        $form = $this->createForm(MicroserviceDeployFormType::class, $microserviceDeployEntity, Array(
            'validation_groups' => Array('microservice_deploy_create')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $microserviceDeployEntity->setActive(true);
                
                // Insert in database
                $this->entityManager->persist($microserviceDeployEntity);
                $this->entityManager->flush();

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_2");
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser()->getRoleUserId());
        
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser()->getRoleUserId());
        
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser()->getRoleUserId());
        
        // Logic
        $microserviceDeployEntity = $this->entityManager->getRepository("App\Entity\MicroserviceDeploy")->find($_SESSION['microserviceDeployProfileId']);
        
        $form = $this->createForm(MicroserviceDeployFormType::class, $microserviceDeployEntity, Array(
            'validation_groups' => Array('microservice_deploy_profile')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $row = $this->query->selectMicroserviceDeployDatabase($_SESSION['microserviceDeployProfileId']);
                
                $pathKeyPublic = $this->utility->getPathSrc() . "/Controller/Microservice/Deploy";
                $pathKeyPrivate = $this->utility->getPathSrc() . "/Controller/Microservice/Deploy";
                
                // Remove old key
                unlink("$pathKeyPublic/{$row['key_public']}");
                unlink("$pathKeyPrivate/{$row['key_private']}");
                
                // Upload key public
                $keyPublic = $microserviceDeployEntity->getKeyPublic();
                $fileName = $keyPublic->getClientOriginalName();
                $keyPublic->move(
                    $pathKeyPublic,
                    $fileName
                );
                $microserviceDeployEntity->setKeyPublic($fileName);
                
                // Upload key private
                $keyPrivate = $microserviceDeployEntity->getKeyPrivate();
                $fileName = $keyPrivate->getClientOriginalName();
                $keyPrivate->move(
                    $pathKeyPrivate,
                    $fileName
                );
                $microserviceDeployEntity->setKeyPrivate($fileName);
                
                // Update in database
                $this->entityManager->persist($microserviceDeployEntity);
                $this->entityManager->flush();

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_5");
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $microserviceDeployRow = $this->query->selectMicroserviceDeployDatabase($request->get("id"));
                
                $sshConnection = $this->sshConnection($microserviceDeployRow, $request);
                
                $this->response['values']['sshConnection'] = $sshConnection;
                
                if ($sshConnection != "")
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_7");
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_8");
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $_SESSION['microserviceDeployProfileId'] : $request->get("id");

                    $microserviceDeployDatabase = $this->microserviceDeployDatabase("delete", $id);

                    if ($microserviceDeployDatabase == true) {
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_9");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $microserviceDeployDatabase = $this->microserviceDeployDatabase("deleteAll", null);

                    if ($microserviceDeployDatabase == true)
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_10");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_11");

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
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['system_user']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_4")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['key_public']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_5")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['key_private']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_6")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['ip']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_7")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['git_user_email']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_8")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['git_user_name']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_9")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['git_clone_url']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_10")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['git_clone_path']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_11")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['user_git_script']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_12")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['user_web_script']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_13")}
                    <span class=\"mdc-list-item__secondary-text\">{$elements[0]['root_web_path']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    <div class=\"mdc-text-field mdc-text-field--outlined mdc-text-field--with-trailing-icon mdc-text-field--dense\">
                        <input class=\"mdc-text-field__input\" type=\"text\" name=\"branchName\" value=\"\" autocomplete=\"off\"/>
                        <i class=\"material-icons mdc-text-field__icon\">textsms</i>
                        <label for=\"form_microservice_deploy_name\" class=\"mdc-floating-label required\">{$this->utility->getTranslator()->trans("microserviceDeployFormType_14")}</label>
                        <div class=\"mdc-notched-outline\">
                            <svg>
                                <path class=\"mdc-notched-outline__path\"/>
                            </svg>
                        </div>
                        <div class=\"mdc-notched-outline__idle\"></div>
                    </div>
                    <span class=\"mdc-list-item__secondary-text\"></span>
                </span>
            </li>
        </ul>
        <button class=\"mdc-button mdc-button--dense mdc-button--raised git_execute\" data-command=\"clone\" type=\"submit\">{$this->utility->getTranslator()->trans("microserviceDeployController_12")}</button>
        <button class=\"mdc-button mdc-button--dense mdc-button--raised git_execute\" data-command=\"pull\" type=\"submit\">{$this->utility->getTranslator()->trans("microserviceDeployController_13")}</button>
        <button class=\"mdc-button mdc-button--dense mdc-button--raised git_execute\" data-command=\"reset\" type=\"submit\">{$this->utility->getTranslator()->trans("microserviceDeployController_14")}</button>";
        
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
                        $listHtml .= $this->utility->getTranslator()->trans("microserviceDeployController_15");
                    else
                        $listHtml .= $this->utility->getTranslator()->trans("microserviceDeployController_16");
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
    
    private function sshConnection($row, $request) {
        $result = "";
        
        if ($request->get("command") == "clone" || $request->get("command") == "pull" || $request->get("command") == "reset") {
            $pathKeyPublic = $this->utility->getPathSrc() . "/Controller/Microservice/Deploy/{$row['key_public']}";
            $pathKeyPrivate = $this->utility->getPathSrc() . "/Controller/Microservice/Deploy/{$row['key_private']}";
            
            $connection = ssh2_connect($row['ip'], 22);
            
            if (ssh2_auth_pubkey_file($connection, $row['system_user'], $pathKeyPublic, $pathKeyPrivate)) {
                $commands = Array();
                
                if ($request->get("command") == "clone") {
                    $commands = Array(
                        "git config --global user.email '{$row['git_user_email']}'",
                        "git config --global user.name '{$row['git_user_name']}'",
                        "cd {$row['git_clone_path']}",
                        "sudo -u {$row['user_git_script']} git clone {$row['git_clone_url']} {$row['git_clone_path']}",
                        "sudo chown -R {$row['user_web_script']} {$row['root_web_path']}",
                        "sudo find {$row['root_web_path']} -type d -exec chmod 775 {} \;",
                        "sudo find {$row['root_web_path']} -type f -exec chmod 664 {} \;"
                    );
                }
                else if ($request->get("command") == "pull") {
                    $commands = Array(
                        "git config --global user.email '{$row['git_user_email']}'",
                        "git config --global user.name '{$row['git_user_name']}'",
                        "cd {$row['git_clone_path']}",
                        "sudo -u user_1 git pull {$row['git_clone_url']} {$request->get("branchName")}",
                        "sudo chown -R {$row['user_web_script']} {$row['root_web_path']}",
                        "sudo find {$row['root_web_path']} -type d -exec chmod 775 {} \;",
                        "sudo find {$row['root_web_path']} -type f -exec chmod 664 {} \;"
                    );
                }
                else if ($request->get("command") == "reset") {
                    $commands = Array(
                        "git config --global user.email '{$row['git_user_email']}'",
                        "git config --global user.name '{$row['git_user_name']}'",
                        "cd {$row['git_clone_path']}",
                        "sudo -u {$row['user_git_script']} git fetch --all",
                        "sudo -u {$row['user_git_script']} git reset --hard {$request->get("branchName")}",
                        "sudo chown -R {$row['user_web_script']} {$row['root_web_path']}",
                        "sudo find {$row['root_web_path']} -type d -exec chmod 775 {} \;",
                        "sudo find {$row['root_web_path']} -type f -exec chmod 664 {} \;"
                    );
                }
                
                $stream = ssh2_exec($connection, implode(";", $commands));
                
                stream_set_blocking($stream, true);
                
                $err_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
                $dio_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
                
                stream_set_blocking($err_stream, true);
                stream_set_blocking($dio_stream, true);
                
                $result .= stream_get_contents($err_stream) . "\r\n";
                $result .= stream_get_contents($dio_stream) . "\r\n";
                
                fclose($stream);
            }
        }
        
        return "<pre>$result</pre>";
    }
}