<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;

use ReinventSoftware\UebusaitoBundle\Entity\User;

use ReinventSoftware\UebusaitoBundle\Form\UserFormType;

class RegistrationController extends Controller {
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
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "registration",
    *   path = "/registration/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/registration.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utilityPrivate->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $userRow = $this->query->selectUserWithHelpCodeDatabase($this->urlExtra);
        
        if ($userRow['id'] == null) {
            $userEntity = new User();
            
            // Form
            $form = $this->createForm(UserFormType::class, $userEntity, Array(
                'validation_groups' => Array('registration')
            ));
            $form->handleRequest($request);
            
            if ($request->isMethod("POST") == true) {
                if ($form->isValid() == true) {
                    $message = $this->utilityPrivate->assigUserPassword("withoutOld", $userEntity, $form);

                    if ($message == "ok") {
                        $settingRow = $this->query->selectSettingDatabase();
                        
                        $this->utilityPrivate->configureUserParameters($userEntity);

                        $helpCode = $this->utility->generateRandomString(20);

                        $userEntity->setDateRegistration(date("Y-m-d H:i:s"));
                        $userEntity->setHelpCode($helpCode);

                        $url = $this->utility->getUrlRoot() . "/" . $request->get("_locale") . "/" . $request->get("urlCurrentPageId") . "/" . $helpCode;

                        $message = "";

                        if ($settingRow['registration_user_confirm_admin'] == true)
                            $message = "<p>" . $this->utility->getTranslator()->trans("registrationController_1") . "</p><a href=\"$url\">$url</a>";
                        else {
                            $message = "<p>" . $this->utility->getTranslator()->trans("registrationController_2") . "</p>";

                            // Send email to admin
                            $this->utility->sendEmail(
                                $settingRow['email_admin'],
                                $this->utility->getTranslator()->trans("registrationController_3"),
                                "<p>" . $this->utility->getTranslator()->trans("registrationController_4") . "<b>" . $userEntity->getUsername() . "</b>. " . $this->utility->getTranslator()->trans("registrationController_5") . "</p>",
                                $_SERVER['SERVER_ADMIN']
                            );
                        }

                        // Send email to user
                        $this->utility->sendEmail(
                            $userEntity->getEmail(),
                            $this->utility->getTranslator()->trans("registrationController_3"),
                            $message,
                            $_SERVER['SERVER_ADMIN']
                        );

                        mkdir("{$this->utility->getPathSrcBundle()}/Resources/files/{$userEntity->getUsername()}");
                        mkdir("{$this->utility->getPathSrcBundle()}/Resources/public/files/{$userEntity->getUsername()}");
                        mkdir("{$this->utility->getPathWebBundle()}/files/{$userEntity->getUsername()}");

                        // Insert in database
                        $this->entityManager->persist($userEntity);
                        $this->entityManager->flush();

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("registrationController_6");
                    }
                    else
                        $this->response['messages']['error'] = $message;
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("registrationController_7");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
                
                return $this->ajax->response(Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));
            }
        }
        else {
            $userEntity = $this->entityManager->getRepository("UebusaitoBundle:User")->find($userRow['id']);
            
            $userEntity->setNotLocked(1);
            $userEntity->setHelpCode(null);
            $userEntity->setCredits(0);
            
            // Insert in database
            $this->entityManager->persist($userEntity);
            $this->entityManager->flush();
            
            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("registrationController_8");
            
            return Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response,
                'form' => null
            );
        }
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response,
            'form' => $form->createView()
        );
    }
    
    // Functions private
}