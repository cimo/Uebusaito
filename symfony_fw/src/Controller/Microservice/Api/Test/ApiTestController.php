<?php
namespace App\Controller\Microservice\Api\Test;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;

use App\Entity\ApiTest;
use App\Form\ApiTestFormType;
use App\Form\ApiTestSelectFormType;

class ApiTestController extends Controller {
    // Vars
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $ajax;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_apiTest_create",
    *   path = "/cp_apiTest_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/test/create.html.twig")
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
        $apiTestEntity = new ApiTest();
        
        $_SESSION['apiTestProfileId'] = 0;
        
        $form = $this->createForm(ApiTestFormType::class, $apiTestEntity, Array(
            'validation_groups' => Array('apiTest_create')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                // Database insert
                $this->entityManager->persist($apiTestEntity);
                $this->entityManager->flush();

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiTestController_1");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiTestController_2");
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
    *   name = "cp_apiTest_select",
    *   path = "/cp_apiTest_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = "", "id" = "0"},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/test/select.html.twig")
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
        $_SESSION['apiTestProfileId'] = 0;
        
        $rows = $this->selectAllApiTestDatabase(true);
        
        $form = $this->createForm(ApiTestSelectFormType::class, null, Array(
            'validation_groups' => Array('apiTest_select'),
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
    *   name = "cp_apiTest_profile",
    *   path = "/cp_apiTest_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/test/profile.html.twig")
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
                    || $this->isCsrfTokenValid("intention", $request->get("form_apiTest_select")['_token']) == true) {
                $id = 0;

                if (empty($request->get("id")) == false)
                    $id = $request->get("id");
                else if (empty($request->get("form_apiTest_select")['id']) == false)
                    $id = $request->get("form_apiTest_select")['id'];

                $apiTestEntity = $this->entityManager->getRepository("App\Entity\ApiTest")->find($id);

                if ($apiTestEntity != null) {
                    $_SESSION['apiTestProfileId'] = $id;

                    $form = $this->createForm(ApiTestFormType::class, $apiTestEntity, Array(
                        'validation_groups' => Array('apiTest_profile')
                    ));
                    $form->handleRequest($request);
                    
                    $this->response['render'] = $this->renderView("@templateRoot/microservice/api/test/profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $form->createView()
                    ));
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiTestController_3");
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
    *   name = "cp_apiTest_profile_save",
    *   path = "/cp_apiTest_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/test/profile.html.twig")
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
        $apiTestEntity = $this->entityManager->getRepository("App\Entity\ApiTest")->find($_SESSION['apiTestProfileId']);
        
        $form = $this->createForm(ApiTestFormType::class, $apiTestEntity, Array(
            'validation_groups' => Array('apiTest_profile')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                // Update database
                $this->entityManager->persist($apiTestEntity);
                $this->entityManager->flush();
                
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiTestController_4");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiTestController_5");
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
    *   name = "cp_apiTest_delete",
    *   path = "/cp_apiTest_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/test/delete.html.twig")
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
                    $id = $request->get("id") == null ? $_SESSION['apiTestProfileId'] : $request->get("id");
                    
                    $apiTestEntity = $this->entityManager->getRepository("App\Entity\ApiTest")->find($id);

                    if ($apiTestEntity != null) {
                        $this->entityManager->remove($apiTestEntity);
                        $this->entityManager->flush();
                        
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiTestController_6");
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiTestController_7");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiTestController_7");

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
    *   name = "apiTest_check",
    *   path = "/apiTest_check",
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/test/index.html.twig")
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
            if ($request->get("event") != null && $request->get("event") == "apiTest_check") {
                $row = $this->selectApiTestDatabase(1);
                $microserviceApiRow = $this->query->selectMicroserviceApiDatabase(1);

                if ($row != false && $microserviceApiRow != false)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiTestController_8");
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiTestController_9");
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiTestController_10");
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
    }
    
    /**
    * @Route(
    *   name = "apiTest_request",
    *   path = "/apiTest_request",
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
            if ($request->get("event") != null && $request->get("event") == "apiTest_request"
                    && $request->get("customerId") != null) {
                $row = $this->selectApiTestDatabase(1);
                $microserviceApiRow = $this->query->selectMicroserviceApiDatabase(1);

                if ($row != false && $microserviceApiRow != false) {
                    $curl = curl_init("https://www.google.com");

                    if ($curl == FALSE)
                        return false;
                    
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

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("apiTestController_11");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiTestController_9");
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("apiTestController_10");
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
    }
    
    // Functions private
    private function selectApiTestDatabase($id) {
        $connection = $this->entityManager->getConnection();
        
        $query = $connection->prepare("SELECT * FROM microservice_apiTest
                                        WHERE id = :id
                                        AND active = :active");
        
        $query->bindValue(":id", $id);
        $query->bindValue(":active", 1);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    private function selectAllApiTestDatabase($bypass = false) {
        $connection = $this->entityManager->getConnection();
        
        if ($bypass == false) {
            $query = $connection->prepare("SELECT * FROM microservice_apiTest
                                            WHERE active = :active");

            $query->bindValue(":active", 1);
        }
        else
            $query = $connection->prepare("SELECT * FROM microservice_apiTest");
        
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