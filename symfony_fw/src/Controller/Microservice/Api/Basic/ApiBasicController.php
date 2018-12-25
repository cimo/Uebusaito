<?php
namespace App\Controller\Microservice\Api\Basic;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;

use App\Entity\ApiBasic;
use App\Form\ApiBasicFormType;
use App\Form\ApiBasicSelectFormType;

class ApiBasicController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    
    private $apiBasicRow;
    
    private $parameters;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_apiBasic_create",
    *   path = "/cp_apiBasic_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/basic/create.html.twig")
    */
    public function createAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        // Logic
        $apiBasicEntity = new ApiBasic();
        
        $_SESSION['apiBasicProfileId'] = 0;
        
        $form = $this->createForm(ApiBasicFormType::class, $apiBasicEntity, Array(
            'validation_groups' => Array('apiBasic_create')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $databasePassword = "";
                
                if ($form->get("databaseUsername")->getData() != "")
                    $databasePassword = $form->get("databasePassword")->getData();
                else
                    $apiBasicEntity->setDatabasePassword("");
                
                // Database insert
                $this->entityManager->persist($apiBasicEntity);
                $this->entityManager->flush();
                
                $this->apiBasicDatabase("update", $databasePassword, $apiBasicEntity->getid());

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiBasicController_1");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_2");
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
    *   name = "cp_apiBasic_select",
    *   path = "/cp_apiBasic_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = "", "id" = "0"},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/basic/select.html.twig")
    */
    public function selectAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
                
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        // Logic
        $_SESSION['apiBasicProfileId'] = 0;
        
        $rows = $this->selectAllApiBasicDatabase(true);
        
        $form = $this->createForm(ApiBasicSelectFormType::class, null, Array(
            'validation_groups' => Array('apiBasic_select'),
            'choicesId' => array_reverse(array_column($rows, "id", "name"), true)
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
    *   name = "cp_apiBasic_profile",
    *   path = "/cp_apiBasic_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/basic/profile.html.twig")
    */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true
                    || $this->isCsrfTokenValid("intention", $request->get("form_apiBasic_select")['_token']) == true) {
                $id = 0;

                if (empty($request->get("id")) == false)
                    $id = $request->get("id");
                else if (empty($request->get("form_apiBasic_select")['id']) == false)
                    $id = $request->get("form_apiBasic_select")['id'];

                $apiBasicEntity = $this->entityManager->getRepository("App\Entity\ApiBasic")->find($id);

                if ($apiBasicEntity != null) {
                    $_SESSION['apiBasicProfileId'] = $id;

                    $form = $this->createForm(ApiBasicFormType::class, $apiBasicEntity, Array(
                        'validation_groups' => Array('apiBasic_profile')
                    ));
                    $form->handleRequest($request);
                    
                    $this->response['render'] = $this->renderView("@templateRoot/microservice/api/basic/profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $form->createView()
                    ));
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_3");
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
    *   name = "cp_apiBasic_profile_save",
    *   path = "/cp_apiBasic_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/basic/profile.html.twig")
    */
    public function profileSaveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        // Logic
        $apiBasicEntity = $this->entityManager->getRepository("App\Entity\ApiBasic")->find($_SESSION['apiBasicProfileId']);
        
        $form = $this->createForm(ApiBasicFormType::class, $apiBasicEntity, Array(
            'validation_groups' => Array('apiBasic_profile')
        ));
        $form->handleRequest($request);
        
        if ($form->get("databaseUsername")->getData() != "" && $form->get("databasePassword")->getData() == "") {
            $apiBasicRow = $this->selectApiBasicDatabase($_SESSION['apiBasicProfileId'], false);
            
            $apiBasicEntity->setDatabasePassword($apiBasicRow['database_password']);
        }
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $databasePassword = "";
                
                if ($form->get("databaseUsername")->getData() != "")
                    $databasePassword = $form->get("databasePassword")->getData();
                else
                    $apiBasicEntity->setDatabasePassword("");
                
                // Update database
                $this->entityManager->persist($apiBasicEntity);
                $this->entityManager->flush();
                
                $this->apiBasicDatabase("update", $databasePassword, $apiBasicEntity->getId());
                
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiBasicController_4");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_5");
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
    *   name = "cp_apiBasic_delete",
    *   path = "/cp_apiBasic_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/basic/delete.html.twig")
    */
    public function deleteAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $_SESSION['apiBasicProfileId'] : $request->get("id");
                    
                    $apiBasicEntity = $this->entityManager->getRepository("App\Entity\ApiBasic")->find($id);

                    if ($apiBasicEntity != null) {
                        // Database remove
                        $this->entityManager->remove($apiBasicEntity);
                        $this->entityManager->flush();
                        
                        $this->apiBasicRequestDatabase("delete", $id);
                        
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiBasicController_6");
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_7");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_7");

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
    
    /**
    * @Route(
    *   name = "cp_apiBasic_log",
    *   path = "/cp_apiBasic_log/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function logAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "log") {
                    $row = $this->selectApiBasicDatabase($_SESSION['apiBasicProfileId'], false);
                    
                    $this->response['values']['log'] = "<pre class=\"api_log\">" . file_get_contents("{$this->utility->getPathSrc()}/files/microservice/api/basic/{$row['name']}.log") . "</pre>";
                }
                    
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
    *   name = "cp_apiBasic_graph",
    *   path = "/cp_apiBasic_graph/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function graphAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "graph") {
                    $this->response['render'] = $this->renderView("@templateRoot/include/chaato.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                    
                    $this->response['values']['selectPeriodYearHtml'] = $this->createSelectPeriodYearHtml($request);
                    $this->response['values']['selectPeriodMonthHtml'] = $this->createSelectPeriodMonthHtml($request);
                    
                    // Label
                    $labelItems = Array(
                        "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31"
                    );
                    
                    // Elements
                    $requestTestActionRows = $this->selectAllApiBasicRequestDatabase($_SESSION['apiBasicProfileId'], "requestTestAction");
                    
                    $elementBasicNames = Array();
                    $elementBasicItems = Array();
                    $countBasic = 0;
                    
                    foreach ($labelItems as $key => $value) {
                        if (isset($requestTestActionRows[$countBasic]) == true) {
                            $dateExplode = explode(" ", $requestTestActionRows[$countBasic]['date']);
                            $date = $dateExplode[0];
                            
                            $dateA = new \DateTime("{$_SESSION['apiBasicGraphPeriod_year']}-{$_SESSION['apiBasicGraphPeriod_month']}-{$labelItems[$key]}");
                            $dateB = new \DateTime($date);
                            
                            if (date_diff($dateA, $dateB)->y == 0 && date_diff($dateA, $dateB)->m == 0 && date_diff($dateA, $dateB)->d == 0) {
                                $elementBasicNames[] = $requestTestActionRows[$countBasic]['name'];
                                $elementBasicItems[] = $requestTestActionRows[$countBasic]['count'];
                                
                                $countBasic ++;
                            }
                            else {
                                $elementBasicNames[] = "";
                                $elementBasicItems[] = "";
                            }
                                
                        }
                        else {
                            $elementBasicNames[] = "";
                            $elementBasicItems[] = "";
                        }
                    }
                    
                    $this->response['values']['json'] = Array(
                        "label" => Array(
                            "name" => "Requests",
                            "items" => $labelItems
                        ),
                        "elements" => Array(
                            Array(
                                "name" => $elementBasicNames,
                                "color" => "#ff0000",
                                "items" => $elementBasicItems
                            )
                        )
                    );
                }
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
    *   name = "apiBasic_requestCheck",
    *   path = "/apiBasic_requestCheck",
    *	methods={"POST", "OPTIONS"}
    * )
    */
    public function requestCheckAction(Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        if ($request->isMethod("POST") == true) {
            $parameters = Array();
            
            if (empty($request->getContent()) == false)
                $parameters = json_decode($request->getContent(), true);
            else
                $parameters = $request->request->all();
            
            $this->parameters = $parameters;
            
            $errorCode = $this->errorCode("requestControl", $parameters);
            
            if ($errorCode == false) {
                if (isset($parameters['event']) == true && $parameters['event'] == "requestCheck") {
                    $microserviceApiRow = $this->query->selectMicroserviceApiDatabase(1);
                    $this->apiBasicRow = $this->selectApiBasicDatabase($parameters['token'], true);

                    if ($microserviceApiRow != false) {
                        if ($this->apiBasicRow != false) {
                            $ipExplode = explode(",", $this->apiBasicRow['ip']);

                            if (isset($this->apiBasicRow['ip']) == true && in_array($_SERVER['REMOTE_ADDR'], $ipExplode) == false)
                                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_13");
                            else
                                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiBasicController_8");
                        }
                        else
                            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_12");
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_9");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_11");
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_11");
        }
        
        $response = new Response(json_encode($this->response));
        $response->headers->set("Access-Control-Allow-Origin", "*");
        $response->headers->set("Access-Control-Allow-Headers", "*");
        $response->headers->set("Access-Control-Allow-Methods", "POST, OPTIONS");
        $response->headers->set("Accept", "application/json");
        $response->headers->set("Content-Type", "application/json");
        
        return $response;
    }
    
    /**
    * @Route(
    *   name = "apiBasic_requestTest",
    *   path = "/apiBasic_requestTest",
    *	methods={"POST", "OPTIONS"}
    * )
    */
    public function requestTestAction(Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $name = "requestTestAction";
        
        if ($request->isMethod("POST") == true) {
            $parameters = Array();

            if (empty($request->getContent()) == false)
                $parameters = json_decode($request->getContent(), true);
            else
                $parameters = $request->request->all();
            
            $this->parameters = $parameters;
            
            $errorCode = $this->errorCode("requestControl", $parameters);
            
            if ($errorCode == false) {
                if (isset($parameters['event']) == true && $parameters['event'] == "requestTest") {
                    $microserviceApiRow = $this->query->selectMicroserviceApiDatabase(1);
                    $this->apiBasicRow = $this->selectApiBasicDatabase($parameters['token'], true);
                    
                    if ($microserviceApiRow != false) {
                        if ($this->apiBasicRow != false) {
                            $ipExplode = explode(",", $this->apiBasicRow['ip']);
                            
                            if (isset($this->apiBasicRow['ip']) == true && in_array($_SERVER['REMOTE_ADDR'], $ipExplode) == false)
                                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_13");
                            else {
                                if ($this->apiBasicRow['url_callback'] != "") {
                                    $urlCallback = $this->urlCallback($parameters, $this->apiBasicRow);
                                    
                                    if ($urlCallback == true) {
                                        $this->saveRequest($this->apiBasicRow['id'], $name, "apiBasic -> $name");
                                        
                                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiBasicController_10");
                                    }
                                    else
                                        $this->response['errorCode'] = 100;
                                }

                                if ($this->apiBasicRow['database_ip'] != "" && $this->apiBasicRow['database_name'] != "" && $this->apiBasicRow['database_username'] != "" && $this->apiBasicRow['database_password'] != "") {
                                    $databaseExternal = $this->databaseExternal($parameters, $this->apiBasicRow);
                                    
                                    if ($databaseExternal != false) {
                                        $this->saveRequest($this->apiBasicRow['id'], $name, "apiBasic -> $name");
                                        
                                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiBasicController_10");
                                    }
                                    else
                                        $this->response['errorCode'] = 100;
                                }
                            }
                        }
                        else
                            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_12");
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_9");
                    
                    file_put_contents("{$this->utility->getPathSrc()}/files/microservice/api/basic/{$this->apiBasicRow['name']}.log", date("Y-m-d H:i:s") . " - $name - IP[{$_SERVER['REMOTE_ADDR']}]: " . print_r($this->response, true) . print_r($parameters, true) . PHP_EOL, FILE_APPEND);
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_11");
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_11");
        }
        
        $response = new Response(json_encode($this->response));
        $response->headers->set("Access-Control-Allow-Origin", "*");
        $response->headers->set("Access-Control-Allow-Headers", "*");
        $response->headers->set("Access-Control-Allow-Methods", "POST, OPTIONS");
        $response->headers->set("Accept", "application/json");
        $response->headers->set("Content-Type", "application/json");
        
        return $response;
    }
    
    // Functions private
    private function apiBasicDatabase($type, $password, $id) {
        if ($password != "" && $id != "") {
            if ($type == "update") {
                $query = $this->utility->getConnection()->prepare("UPDATE IGNORE microservice_apiBasic
                                                                    SET database_password = AES_ENCRYPT(:password, 'secret')
                                                                    WHERE id = :id");
                
                $query->bindValue(":password", $password);
                $query->bindValue(":id", $id);
                
                return $query->execute();
            }
            else if ($type == "select") {
                $query = $this->utility->getConnection()->prepare("SELECT AES_DECRYPT(:password, 'secret') AS database_password FROM microservice_apiBasic
                                                                    WHERE id = :id");
                
                $query->bindValue(":password", $password);
                $query->bindValue(":id", $id);
                
                $query->execute();
                
                return $query->fetch();
            }
        }
        
        return false;
    }
    
    private function apiBasicRequestDatabase($type, $id, $name = "", $row = null) {
        if ($type == "insert") {
            $query = $this->utility->getConnection()->prepare("INSERT INTO microservice_apiBasic_request (
                                                                    api_id,
                                                                    name,
                                                                    date,
                                                                    ip
                                                                )
                                                                VALUES (
                                                                    :apiId,
                                                                    :name,
                                                                    :date,
                                                                    :ip
                                                                );");
            
            $query->bindValue(":apiId", $id);
            $query->bindValue(":name", $name);
            $query->bindValue(":date", date("Y-m-d H:i:s"));
            $query->bindValue(":ip", $_SERVER['REMOTE_ADDR']);
        }
        else if ($type == "update") {
            $query = $this->utility->getConnection()->prepare("UPDATE microservice_apiBasic_request
                                                                SET count = :count
                                                                WHERE api_id = :apiId
                                                                AND name = :name
                                                                AND date LIKE :date");
            
            $query->bindValue(":count", $row['count'] + 1);
            $query->bindValue(":apiId", $id);
            $query->bindValue(":name", $name);
            $query->bindValue(":date", "%" . date("Y-m-d") . "%");
        }
        else if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM microservice_apiBasic_request
                                                                WHERE api_id = :id");
            
            $query->bindValue(":id", $id);
        }
        
        return $query->execute();
    }
    
    private function selectApiBasicDatabase($value, $onlyActive) {
        $connection = $this->entityManager->getConnection();
        
        if (is_numeric($value) == true) {
            if ($onlyActive == true) {
                $query = $connection->prepare("SELECT * FROM microservice_apiBasic
                                                WHERE id = :id
                                                AND active = :active");
                
                $query->bindValue(":active", 1);
            }
            else {
                $query = $connection->prepare("SELECT * FROM microservice_apiBasic
                                                WHERE id = :id");
            }
            
            $query->bindValue(":id", $value);
        }
        else {
            if ($onlyActive == true) {
                $query = $connection->prepare("SELECT * FROM microservice_apiBasic
                                                WHERE token = :token
                                                AND active = :active");
                
                $query->bindValue(":active", 1);
            }
            else {
                $query = $connection->prepare("SELECT * FROM microservice_apiBasic
                                                WHERE token = :token");
            }
            
            $query->bindValue(":token", $value);
        }
        
        $query->execute();
        
        return $query->fetch();
    }
    
    private function selectAllApiBasicDatabase($onlyActive = false) {
        $connection = $this->entityManager->getConnection();
        
        if ($onlyActive == false) {
            $query = $connection->prepare("SELECT * FROM microservice_apiBasic
                                            WHERE active = :active");

            $query->bindValue(":active", 1);
        }
        else
            $query = $connection->prepare("SELECT * FROM microservice_apiBasic");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    private function selectApiBasicRequestDatabase($id, $name) {
        $connection = $this->entityManager->getConnection();
        
        $query = $connection->prepare("SELECT * FROM microservice_apiBasic_request
                                        WHERE api_id = :apiId
                                        AND name = :name
                                        AND date LIKE :date");
        
        $query->bindValue(":apiId", $id);
        $query->bindValue(":name", $name);
        $query->bindValue(":date", "%" . date("Y-m-d") . "%");
        
        $query->execute();
        
        return $query->fetch();
    }
    
    private function selectAllApiBasicRequestDatabase($id, $name) {
        $connection = $this->entityManager->getConnection();
        
        $query = $connection->prepare("SELECT * FROM microservice_apiBasic_request
                                        WHERE api_id = :apiId
                                        AND name = :name
                                        AND date LIKE :date");
        
        $query->bindValue(":apiId", $id);
        $query->bindValue(":name", $name);
        $query->bindValue(":date", "%{$_SESSION['apiBasicGraphPeriod_year']}-{$_SESSION['apiBasicGraphPeriod_month']}%");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    private function createSelectPeriodYearHtml($request) {
        $periodMin = new \DateTime("2017/01/01");
        $periodMax = new \DateTime(date("Y/01/01"));
        
        $difference = date_diff($periodMin, $periodMax)->y;
        
        $html = "<div style=\"width: 100px;\" class=\"mdc-select\">
            <select class=\"mdc-select__native-control graph_period_year\" name=\"graph_period_year\">";
            
            for ($a = 0; $a < $difference; $a ++) {
                $date = $periodMin->add(new \DateInterval("P1Y"));
                
                $year = date("Y");

                if ($request->get("year") != null && $request->get("year") != "")
                    $year = $request->get("year");
                else if (isset($_SESSION['apiBasicGraphPeriod_year']) == true)
                    $year = $_SESSION['apiBasicGraphPeriod_year'];

                $_SESSION["apiBasicGraphPeriod_year"] = $year;
                
                $periodMax = new \DateTime(date("$year/01/01"));
                
                $selected = "";
                
                if (date_diff($periodMin, $periodMax)->y == 0)
                    $selected = "selected=\"selected\"";
                
                $html .= "<option $selected value=\"{$date->format("Y")}\">{$date->format("Y")}</option>";
            }
            
            $html .= "</select>
            <label class=\"mdc-floating-label mdc-floating-label--float-above\">{$this->utility->getTranslator()->trans("apiBasicController_14")}</label>
            <div class=\"mdc-line-ripple\"></div>
        </div>";
        
        return $html;
    }
    
    private function createSelectPeriodMonthHtml($request) {
        $month = date("m");
        
        if ($request->get("month") != null && $request->get("month") != "")
            $month = $request->get("month");
        else if (isset($_SESSION['apiBasicGraphPeriod_month']) == true)
            $month = $_SESSION['apiBasicGraphPeriod_month'];
        
        $_SESSION["apiBasicGraphPeriod_month"] = $month;
        
        $html = "<div style=\"width: 100px;\" class=\"mdc-select\">
            <select class=\"mdc-select__native-control graph_period_month\" name=\"graph_period_month\">";
            
            for ($a = 1; $a <= 12; $a ++) {
                $aTmp = sprintf("%02d", $a);
                
                $selected = "";
                
                if ($aTmp == $month)
                    $selected = "selected=\"selected\"";
                
                $html .= "<option $selected value=\"$aTmp\">$aTmp</option>";
            }
            
            $html .= "</select>
            <label class=\"mdc-floating-label mdc-floating-label--float-above\">{$this->utility->getTranslator()->trans("apiBasicController_15")}</label>
            <div class=\"mdc-line-ripple\"></div>
        </div>";
        
        return $html;
    }
    
    private function errorCode($type, $parameters) {
        $errorCode = 0;
        
        if ($type == "requestControl") {
            if (isset($parameters['event']) == false || $parameters['event'] == "")
                $errorCode = 1;
            if (isset($parameters['token']) == false || $parameters['token'] == "")
                $errorCode = 2;
        }
        
        $this->response['errorCode'] = $errorCode;
        
        return $errorCode;
    }
    
    private function urlCallback($parameters, $row) {
        $curl = curl_init();
        
        if ($curl == false)
            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_16");
        else {
            $postFields = Array(
                'event' => 'apiBasic'
            );

            curl_setopt($curl, CURLOPT_URL, $row['url_callback']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_ENCODING, "");
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($curl, CURLOPT_TIMEOUT, 120);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
            curl_setopt($curl, CURLOPT_HTTPHEADER, Array(
                "Content-Type: application/json"
            ));

            $curlResponse = curl_exec($curl);
            $curlInfo = curl_getinfo($curl);
            curl_close($curl);
            
            if ($curlInfo['http_code'] == "200") {
                $this->response['messages']['urlCallbackResponse'] = $curlResponse;
                $this->response['messages']['urlCallbackInfo'] = $curlInfo;

                return true;
            }
        }
        
        return false;
    }
    
    private function databaseExternal($parameters, $row) {
        $response = false;
        
        $apiBasicRow = $this->apiBasicDatabase("select", $row['database_password'], $row['id']);
        
        if ($row['database_ip'] != "" && $row['database_name'] != "" && $row['database_username'] != "" && $apiBasicRow['database_password'] != "") {
            $checkHost = $this->utility->checkHost($row['database_ip']);
            
            if ($checkHost == false)
                return $response;
            
            $pdo = new \PDO("mysql:host={$row['database_ip']};dbname={$row['database_name']};charset=utf8", $row['database_username'], $apiBasicRow['database_password']);
            
            //...
            
            unset($pdo);
        }
        
        return $response;
    }
    
    private function saveRequest($id, $name, $message) {
        $requestRow = $this->selectApiBasicRequestDatabase($id, $name);
        
        if ($requestRow == false)
            $this->apiBasicRequestDatabase("insert", $id, $name);
        else
            $this->apiBasicRequestDatabase("update", $id, $name, $requestRow);
        
        if ($this->apiBasicRow['slack_active'] == true)
            $this->utility->sendMessageToSlackRoom("slack_iw_apiBasic", date("Y-m-d H:i:s") . " - IP[{$_SERVER['REMOTE_ADDR']}] - Message: $message");
        
        if ($this->apiBasicRow['line_active'] == true)
            $this->utility->sendMessageToLineChat("line_iw_apiBasic", date("Y-m-d H:i:s") . " - IP[{$_SERVER['REMOTE_ADDR']}] - Message: $message");
    }
}