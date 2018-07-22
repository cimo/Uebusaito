<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\Upload;

use App\Form\UserFormType;
use App\Form\PasswordFormType;
use App\Form\CreditFormType;

class ProfileController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    private $upload;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_profile",
    *   path = "/cp_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
	*	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/profile.html.twig")
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_USER"), $this->getUser()->getRoleUserId());
        
        // Logic
        $usernameOld = $this->getUser()->getUsername();
        
        $avatar = "{$this->utility->getUrlRoot()}/files/$usernameOld/Avatar.jpg";
        
        $form = $this->createForm(UserFormType::class, $this->getUser(), Array(
            'validation_groups' => Array('profile')
        ));
        
        if ($this->getUser()->getId() > 1)
            $form->remove("username");
        
        $form->remove("roleUserId");
        $form->remove("password");
        $form->remove("notLocked");
        
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isValid() == true) {
                if ($form->has("username") == true) {
                    if (file_exists("{$this->utility->getPathWeb()}/files/$usernameOld") == true) {
                        @rename("{$this->utility->getPathWeb()}/files/$usernameOld",
                                "{$this->utility->getPathWeb()}/files/{$form->get("username")->getData()}");
					}
                }
                
                // Insert in database
                $this->entityManager->persist($this->getUser());
                $this->entityManager->flush();

                if ($form->has("username") == true && $form->get("username")->getData() != $usernameOld) {
                    $this->utility->getTokenStorage()->setToken(null);
                    
                    $message = $this->utility->getTranslator()->trans("profileController_1");
                    
                    $_SESSION['userActivity'] = $message;
                    
                    $this->response['messages']['info'] = $message;
                }
                else
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("profileController_2");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("profileController_3");
                $this->response['errors'] = $this->ajax->errors($form);
            }
            
            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response,
                'avatar' => $avatar
            ));
        }
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response,
            'form' => $form->createView(),
            'avatar' => $avatar
        );
    }
    
    /**
    * @Route(
    *   name = "cp_profile_password",
    *   path = "/cp_profile_password/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
	*	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/profile_password.html.twig")
    */
    public function passwordAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_USER"), $this->getUser()->getRoleUserId());
        
        // Logic
        $form = $this->createForm(PasswordFormType::class, null, Array(
            'validation_groups' => Array('profile_password')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isValid() == true) {
                $messagePassword = $this->utility->assignUserPassword("withOld", $this->getUser(), $form);

                if ($messagePassword == "ok") {
                    // Insert in database
                    $this->entityManager->persist($this->getUser());
                    $this->entityManager->flush();

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("profileController_4");
                }
                else
                    $this->response['messages']['error'] = $messagePassword;
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("profileController_5");
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
    *   name = "cp_profile_credit",
    *   path = "/cp_profile_credit/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
	*	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/profile_credit.html.twig")
    */
    public function creditAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_USER"), $this->getUser()->getRoleUserId());
        
        // Logic
        $settingRow = $this->query->selectSettingDatabase();
        
        $form = $this->createForm(CreditFormType::class, null, Array(
            'validation_groups' => Array('profile_credit')
        ));
        $form->handleRequest($request);
        
        $this->response['values']['currentCredit'] = $this->getUser() != null ? $this->getUser()->getCredit() : 0;
        $this->response['values']['payPalSandbox'] = $settingRow['payPal_sandbox'];
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isValid() == true)
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("profileController_6");
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("profileController_7");
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
    *   name = "cp_profile_credit_payPal",
    *   path = "/cp_profile_credit_payPal/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
	*	methods={"POST"}
    * )
    */
    public function creditPayPalAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        // Logic
        return new Response();
    }
    
    /**
    * @Route(
    *   name = "cp_profile_upload",
    *   path = "/cp_profile_upload/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
	*	methods={"POST"}
    * )
    */
    public function uploadAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->upload = new Upload($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        // Logic
        $path = "";

        if ($this->getUser() != null)
            $path = "{$this->utility->getPathWeb()}/files/{$this->getUser()->getUsername()}";
        
        $this->upload->setSettings(Array(
            'path' => $path,
            'chunkSize' => 1000000,
            'inputType' => "single",
            'types' => Array('image/jpg', 'image/jpeg', 'image/png'),
            'maxSize' => 2097152,
            'nameOverwrite' => "Avatar",
            'imageWidth' => 150,
            'imageHeight' => 150
        ));
        $this->response['upload']['processFile'] = $this->upload->processFile();
        
        return $this->ajax->response(Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        ));
    }
    
    // Functions private
}