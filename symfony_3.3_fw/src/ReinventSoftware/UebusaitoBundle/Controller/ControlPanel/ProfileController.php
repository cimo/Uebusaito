<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Upload;

use ReinventSoftware\UebusaitoBundle\Form\UserFormType;
use ReinventSoftware\UebusaitoBundle\Form\PasswordFormType;
use ReinventSoftware\UebusaitoBundle\Form\CreditsFormType;

class ProfileController extends Controller {
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
    private $upload;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_profile",
    *   path = "/cp_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/profile.html.twig")
    */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        $usernameOld = $this->getUser()->getUsername();
        
        $avatar = "{$this->utility->getUrlWebBundle()}/files/$usernameOld/Avatar.jpg";
        
        // Form
        $form = $this->createForm(UserFormType::class, $this->getUser(), Array(
            'validation_groups' => Array('profile')
        ));
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        if ($chekRoleLevel == false)
            $form->remove("username");
        
        $form->remove("roleId");
        $form->remove("password");
        $form->remove("notLocked");
        
        $form->handleRequest($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_USER"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($form->isValid() == true) {
                if (file_exists("{$this->utility->getPathSrcBundle()}/Resources/files/$usernameOld") == true)
                    @rename("{$this->utility->getPathSrcBundle()}/Resources/files/$usernameOld",
                            "{$this->utility->getPathSrcBundle()}/Resources/files/{$form->get("username")->getData()}");

                if (file_exists("{$this->utility->getPathSrcBundle()}/Resources/public/files/$usernameOld") == true)
                    @rename("{$this->utility->getPathSrcBundle()}/Resources/public/files/$usernameOld",
                            "{$this->utility->getPathSrcBundle()}/Resources/public/files/{$form->get("username")->getData()}");

                if (file_exists("{$this->utility->getPathWebBundle()}/files/$usernameOld") == true)
                    @rename("{$this->utility->getPathWebBundle()}/files/$usernameOld",
                            "{$this->utility->getPathWebBundle()}/files/{$form->get("username")->getData()}");

                // Insert in database
                $this->entityManager->persist($this->getUser());
                $this->entityManager->flush();

                if ($form->get("username")->getData() != $usernameOld) {
                    $this->utility->getTokenStorage()->setToken(null);
                    
                    $message = $this->utility->getTranslator()->trans("profileController_1");
                    
                    $_SESSION['count_root'] = 1;
                    $_SESSION['user_activity'] = $message;
                    
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
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/profile_password.html.twig")
    */
    public function passwordAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        // Form
        $form = $this->createForm(PasswordFormType::class, null, Array(
            'validation_groups' => Array('profile_password')
        ));
        $form->handleRequest($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_USER"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($form->isValid() == true) {
                $message = $this->utilityPrivate->assigUserPassword("withOld", $this->getUser(), $form);

                if ($message == "ok") {
                    // Insert in database
                    $this->entityManager->persist($this->getUser());
                    $this->entityManager->flush();

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("profileController_4");
                }
                else
                    $this->response['messages']['error'] = $message;
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
    *   name = "cp_profile_credits",
    *   path = "/cp_profile_credits/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/profile_credits.html.twig")
    */
    public function creditsAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        $settingRow = $this->query->selectSettingDatabase();
        
        $this->response['values']['currentCredits'] = $this->getUser() != null ? $this->getUser()->getCredits() : 0;
        $this->response['values']['payPalSandbox'] = $settingRow['payPal_sandbox'];
        
        // Form
        $form = $this->createForm(CreditsFormType::class, null, Array(
            'validation_groups' => Array('profile_credits')
        ));
        $form->handleRequest($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_USER"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
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
    *   name = "cp_profile_credits_payPal",
    *   path = "/cp_profile_credits_payPal/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    */
    public function creditsPayPalAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        return new Response();
    }
    
    /**
    * @Route(
    *   name = "cp_profile_upload",
    *   path = "/cp_profile_upload/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    */
    public function uploadAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->upload = new Upload($this->container, $this->entityManager);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        $path = "";

        if ($this->getUser() != null)
            $path = "{$this->utility->getPathSrcBundle()}/Resources/public/files/{$this->getUser()->getUsername()}";

        $this->response['upload']['inputType'] = "single";
        $this->response['upload']['maxSize'] = 2097152;
        $this->response['upload']['type'] = Array('image/jpeg', 'image/png', 'image/gif');
        $this->response['upload']['chunkSize'] = 1000000;
        $this->response['upload']['nameOverwrite'] = "Avatar";
        $this->response['upload']['imageWidth'] = 250;
        $this->response['upload']['imageHeight'] = 250;

        $this->response['upload']['processFile'] = $this->upload->processFile(
            $path,
            $this->response['upload']['inputType'],
            $this->response['upload']['maxSize'],
            $this->response['upload']['type'],
            $this->response['upload']['chunkSize'],
            $this->response['upload']['nameOverwrite'],
            $this->response['upload']['imageWidth'],
            $this->response['upload']['imageHeight']
        );
        
        return $this->ajax->response(Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        ));
    }
    
    // Functions private
}