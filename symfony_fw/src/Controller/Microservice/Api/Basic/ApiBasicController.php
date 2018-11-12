<?php
namespace App\Controller\Microservice\Api\Basic;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;

use App\Entity\ApiBasic;
use App\Form\ApiBasicFormType;
use App\Form\ApiBasicSelectFormType;

class ApiBasicController extends Controller {
    // Vars
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $ajax;
    
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
    public function createAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        $apiBasicEntity = new ApiBasic();
        
        $_SESSION['apiBasicProfileId'] = 0;
        
        $form = $this->createForm(ApiBasicFormType::class, $apiBasicEntity, Array(
            'validation_groups' => Array('apiBasic_create')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                // Database insert
                $this->entityManager->persist($apiBasicEntity);
                $this->entityManager->flush();

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
    public function selectAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
    public function profileSaveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        $apiBasicEntity = $this->entityManager->getRepository("App\Entity\ApiBasic")->find($_SESSION['apiBasicProfileId']);
        
        $form = $this->createForm(ApiBasicFormType::class, $apiBasicEntity, Array(
            'validation_groups' => Array('apiBasic_profile')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                // Update database
                $this->entityManager->persist($apiBasicEntity);
                $this->entityManager->flush();
                
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
                    $id = $request->get("id") == null ? $_SESSION['apiBasicProfileId'] : $request->get("id");
                    
                    $apiBasicEntity = $this->entityManager->getRepository("App\Entity\ApiBasic")->find($id);

                    if ($apiBasicEntity != null) {
                        $this->entityManager->remove($apiBasicEntity);
                        $this->entityManager->flush();
                        
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
    public function logAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
                if ($request->get("event") == "log")
                    $this->response['values']['log'] = "<pre class=\"apiBasic_log\">" . file_get_contents("{$this->utility->getPathSrc()}/files/microservice/api/apiBasic_1.log") . "</pre>";
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
    *   name = "apiBasic_check",
    *   path = "/apiBasic_check",
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/basic/index.html.twig")
    */
    public function checkAction(Request $request) {
        header("Access-Control-Allow-Origin: *");
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        // Logic
        if ($request->isMethod("POST") == true) {
            if ($request->get("event") != null && $request->get("event") == "apiBasic_check") {
                $microserviceApiRow = $this->query->selectMicroserviceApiDatabase(1);
                $apiBasicRow = $this->selectApiBasicDatabase($request->get("token"));
                
                if ($microserviceApiRow != false) {
                    if ($apiBasicRow != false) {
                        if ($apiBasicRow['ip'] != "" && $apiBasicRow['ip'] != $_SERVER['REMOTE_ADDR'])
                            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_14");
                        else
                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiBasicController_8");
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_13");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_9");
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_12");
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
    }
    
    /**
    * @Route(
    *   name = "apiBasic_request",
    *   path = "/apiBasic_request",
    *	methods={"POST"}
    * )
    */
    public function requestAction(Request $request) {
        header("Access-Control-Allow-Origin: *");
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        // Logic
        if ($request->isMethod("POST") == true) {
            if ($request->get("event") != null && $request->get("event") == "apiBasic_request") {
                $microserviceApiRow = $this->query->selectMicroserviceApiDatabase(1);
                $apiBasicRow = $this->selectApiBasicDatabase($request->get("token"));
                
                if ($microserviceApiRow != false) {
                    if ($apiBasicRow != false) {
                        if ($apiBasicRow['ip'] != "" && $apiBasicRow['ip'] != $_SERVER['REMOTE_ADDR'])
                            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_14");
                        else {
                            if ($apiBasicRow['url_callback'] != "") {
                                $curl = curl_init($apiBasicRow['url_callback']);

                                if ($curl == FALSE)
                                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_15");
                                else {
                                    $postFields = "customerId=" . $request->get("customerId");

                                    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                                    curl_setopt($curl, CURLOPT_POST, 1);
                                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                                    curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
                                    curl_setopt($curl, CURLOPT_SSLVERSION, 6);
                                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
                                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

                                    curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
                                    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
                                    curl_setopt($curl, CURLOPT_HTTPHEADER, Array('Connection: Close'));

                                    $curlResponse = curl_exec($curl);
                                    $curlInfo = curl_getinfo($curl);
                                    curl_close($curl);
                                }
                            }
                            
                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiBasicController_11");
                        }
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_13");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_9");
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiBasicController_12");
            
            file_put_contents("{$this->utility->getPathSrc()}/files/microservice/api/apiBasic_1.log", date("Y-m-d H:i e") . " - IP[{$_SERVER['REMOTE_ADDR']}]: " . print_r($this->response['messages'], true) . PHP_EOL, FILE_APPEND);
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
    }
    
    // Functions private
    private function selectApiBasicDatabase($value) {
        $connection = $this->entityManager->getConnection();
        
        if (is_numeric($value) == true) {
            $query = $connection->prepare("SELECT * FROM microservice_apiBasic
                                            WHERE id = :id
                                            AND active = :active");
            
            $query->bindValue(":id", $value);
        }
        else {
            $query = $connection->prepare("SELECT * FROM microservice_apiBasic
                                            WHERE token = :token
                                            AND active = :active");
            
            $query->bindValue(":token", $value);
        }
        
        $query->bindValue(":active", 1);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    private function selectAllApiBasicDatabase($bypass = false) {
        $connection = $this->entityManager->getConnection();
        
        if ($bypass == false) {
            $query = $connection->prepare("SELECT * FROM microservice_apiBasic
                                            WHERE active = :active");

            $query->bindValue(":active", 1);
        }
        else
            $query = $connection->prepare("SELECT * FROM microservice_apiBasic");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function createSelectHtml($rows, $selectId, $label) {
        $html = "<div id=\"$selectId\" class=\"mdc-select\">
            <select class=\"mdc-select__native-control\">
                <option value=\"\"></option>";
                foreach ($rows as $key => $value) {
                    $html .= "<option value=\"$key\">$value</option>";
                }
            $html .= "</select>
            <label class=\"mdc-floating-label mdc-floating-label--float-above\">$label</label>
            <div class=\"mdc-line-ripple\"></div>
        </div>";
        
        return $html;
    }
}