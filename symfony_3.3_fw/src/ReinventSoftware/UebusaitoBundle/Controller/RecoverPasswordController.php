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

use ReinventSoftware\UebusaitoBundle\Form\RecoverPasswordFormType;
use ReinventSoftware\UebusaitoBundle\Form\ChangePasswordFormType;

class RecoverPasswordController extends Controller {
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
    *   name = "recover_password",
    *   path = "/recover_password/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/recover_password.html.twig")
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
            $this->response['values']['userId'] = $userRow['id'];
            
            // Form
            $form = $this->createForm(RecoverPasswordFormType::class, null, Array(
                'validation_groups' => Array('recover_password')
            ));
            $form->handleRequest($request);
            
            if ($request->isMethod("POST") == true) {
                if ($form->isValid() == true) {
                    $email = $form->get("email")->getData();

                    $userEntity = $this->entityManager->getRepository("UebusaitoBundle:User")->loadUserByUsername($email);

                    if ($userEntity != null) {
                        $helpCode = $this->utility->generateRandomString(20);

                        $userEntity->setHelpCode($helpCode);

                        $url = $this->utility->getUrlRoot() . "/" . $requet->get("_locale") . "/" . $request->get("urlCurrentPageId") . "/" . $helpCode;

                        // Send email to user
                        $this->utility->sendEmail($userEntity->getEmail(),
                                                    "Recover password",
                                                    "<p>Click on this link for reset your password:</p>" .
                                                    "<a href=\"$url\">$url</a>",
                                                    $_SERVER['SERVER_ADMIN']);

                        // Insert in database
                        $this->entityManager->persist($userEntity);
                        $this->entityManager->flush();

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("recoverPasswordController_1");
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("recoverPasswordController_2");
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("recoverPasswordController_3");
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
            
            $this->response['values']['userId'] = $userEntity->getId();
            
            // Form
            $form = $this->createForm(ChangePasswordFormType::class, null, Array(
                'validation_groups' => Array('change_password')
            ));
            $form->handleRequest($request);
            
            if ($request->isMethod("POST") == true) {
                if ($form->isValid() == true) {
                    $message = $this->utilityPrivate->assigUserPassword("withoutOld", $userEntity, $form);

                    if ($message == "ok") {
                        $userEntity->setHelpCode(null);

                        // Insert in database
                        $this->entityManager->persist($userEntity);
                        $this->entityManager->flush();

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("recoverPasswordController_4");
                    }
                    else
                        $this->response['messages']['error'] = $message;
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("recoverPasswordController_5");
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