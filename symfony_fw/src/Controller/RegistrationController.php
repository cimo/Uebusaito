<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;

use App\Entity\User;

use App\Form\UserFormType;

class RegistrationController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "registration",
    *   path = "/registration/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/registration.html.twig")
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
        
        // Logic
        $userRow = $this->query->selectUserWithHelpCodeDatabase($this->urlExtra);
        
        if ($userRow == false) {
            $userEntity = new User();
            
            $form = $this->createForm(UserFormType::class, $userEntity, Array(
                'validation_groups' => Array('registration')
            ));
            $form->handleRequest($request);
            
            if ($request->isMethod("POST") == true) {
                if ($form->isSubmitted() == true && $form->isValid() == true) {
                    $messagePassword = $this->utility->assignUserPassword("withoutOld", $userEntity, $form);

                    if ($messagePassword == "ok") {
                        $settingRow = $this->query->selectSettingDatabase();
                        
                        $this->utility->assignUserParameter($userEntity);

                        $helpCode = $this->utility->generateRandomString(20);

                        $userEntity->setDateRegistration(date("Y-m-d H:i:s"));
                        $userEntity->setHelpCode($helpCode);

                        $url = $this->utility->getUrlRoot() . $this->utility->getWebsiteFile() . "/" . $request->get("_locale") . "/" . $request->get("urlCurrentPageId") . "/" . $helpCode;

                        $messageEmail = "";

                        if ($settingRow['registration_user_confirm_admin'] == true)
                            $messageEmail = "<p>" . $this->utility->getTranslator()->trans("registrationController_1") . "</p><a href=\"$url\">$url</a>";
                        else {
                            $messageEmail = "<p>" . $this->utility->getTranslator()->trans("registrationController_2") . "</p>";

                            // Send email to admin
                            $this->utility->sendEmail(
                                $settingRow['email_admin'],
                                $this->utility->getTranslator()->trans("registrationController_3"),
                                "<p>" . $this->utility->getTranslator()->trans("registrationController_4") . "<b>" . $userEntity->getUsername() . "</b>. " . $this->utility->getTranslator()->trans("registrationController_5") . "</p>",
                                $settingRow['email_admin']
                            );
                        }

                        // Send email to user
                        $this->utility->sendEmail(
                            $userEntity->getEmail(),
                            $this->utility->getTranslator()->trans("registrationController_3"),
                            $messageEmail,
                            $settingRow['email_admin']
                        );

                        mkdir("{$this->utility->getPathWeb()}/files/{$userEntity->getUsername()}");

                        // Database insert
                        $this->entityManager->persist($userEntity);
                        $this->entityManager->flush();

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("registrationController_6");
                    }
                    else
                        $this->response['messages']['error'] = $messagePassword;
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
            $userEntity = $this->entityManager->getRepository("App\Entity\User")->find($userRow['id']);
            
            $userEntity->setActive(1);
            $userEntity->setHelpCode(null);
            $userEntity->setCredit(0);
            
            // Database insert
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