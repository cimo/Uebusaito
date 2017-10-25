<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UebusaitoUtility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\TableAndPagination;

use ReinventSoftware\UebusaitoBundle\Form\PaymentsUserSelectionFormType;
use ReinventSoftware\UebusaitoBundle\Form\PaymentsSelectionFormType;

class PaymentController extends Controller {
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
    *   name = "cp_payments_user_selection",
    *   path = "/cp_payments_user_selection/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/payments_user_selection.html.twig")
    */
    public function userSelectionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Logic
        if (isset($_SESSION['payments_user_id']) == false)
            $_SESSION['payments_user_id'] = $this->getUser()->getId();
        
        $form = $this->createForm(PaymentsUserSelectionFormType::class, null, Array(
            'validation_groups' => Array('payments_user_selection'),
            'choicesId' => array_column($this->query->selectAllUsersDatabase(), "id", "username")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($form->isValid() == true) {
                if ($form->get("userId")->getData() != null) {
                    $_SESSION['payments_user_id'] = $form->get("userId")->getData();
                    
                    $this->response['messages']['success'] = "";
                    
                    return $this->ajax->response(Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                }
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("paymentController_1");
                $this->response['errors'] = $this->ajax->errors($form);
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
    *   name = "cp_payments_selection",
    *   path = "/cp_payments_selection/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/payments_selection.html.twig")
    */
    public function selectionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->tableAndPagination = new TableAndPagination($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Logic
        if (isset($_SESSION['payments_user_id']) == false)
            $_SESSION['payments_user_id'] = $this->getUser()->getId();
        
        $paymentRows = $this->query->selectAllPaymentsDatabase($_SESSION['payments_user_id']);

        $tableAndPagination = $this->tableAndPagination->request($paymentRows, 20, "payment", true, true);

        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['list'] = $this->createHtmlList($tableAndPagination['list']);
        
        $form = $this->createForm(PaymentsSelectionFormType::class, null, Array(
            'validation_groups' => Array('payments_selection'),
            'choicesId' => array_reverse(array_column($this->query->selectAllPaymentsDatabase($_SESSION['payments_user_id']), "id", "transaction"), true)
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($this->utility->checkToken($request) == true) {
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
    *   name = "cp_payment_profile_result",
    *   path = "/cp_payment_profile_result/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/payment_profile.html.twig")
    */
    public function profileResultAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        // Logic
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            $id = 0;
            
            if (empty($request->get("id")) == false)
                $id = $request->get("id");
            else if (empty($request->get("form_payments_selection")['id']) == false)
                $id = $request->get("form_payments_selection")['id'];
            
            $paymentEntity = $this->entityManager->getRepository("UebusaitoBundle:Payment")->find($id);
            
            if ($paymentEntity != null) {
                $_SESSION['payment_profile_id'] = $id;
                
                $this->response['values']['payment'] = $paymentEntity;
                
                $this->response['render'] = $this->renderView("@UebusaitoBundleViews/render/control_panel/payment_profile.html.twig", Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("paymentController_2");
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
    *   name = "cp_payment_deletion",
    *   path = "/cp_payment_deletion/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"POST"})
    * @Template("@UebusaitoBundleViews/render/control_panel/payment_deletion.html.twig")
    */
    public function deletionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $chekRoleLevel = $this->uebusaitoUtility->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        // Logic
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($request->get("event") == "delete" && $this->utility->checkToken($request) == true) {
                $id = $request->get("id") == null ? $_SESSION['payment_profile_id'] : $request->get("id");
                
                $paymentsDatabase = $this->paymentsDatabase("delete", $id);

                if ($paymentsDatabase == true) {
                    $this->response['values']['id'] = $id;
                    
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("paymentController_3");
                }
            }
            else if ($request->get("event") == "deleteAll" && $this->utility->checkToken($request) == true) {
                $paymentsDatabase = $this->paymentsDatabase("deleteAll", null);

                if ($paymentsDatabase == true)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("paymentController_4");
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("paymentController_5");
            
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
            'response' => $this->response
        );
    }
    
    // Functions private
    private function createHtmlList($elements) {
        $listHtml = "";
        
        foreach ($elements as $key => $value) {
            $listHtml .= "<tr>
                <td class=\"id_column\">
                    {$value['id']}
                </td>
                <td class=\"checkbox_column\">
                    <input class=\"display_inline margin_clear\" type=\"checkbox\"/>
                </td>
                <td>
                    {$value['transaction']}
                </td>
                <td>
                    {$value['date']}
                </td>
                <td>
                    {$value['status']}
                </td>
                <td>
                    {$value['payer']}
                </td>
                <td class=\"horizontal_center\">
                    <button class=\"cp_payment_deletion button_custom_danger\"><i class=\"fa fa-remove\"></i></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function paymentsDatabase($type, $id) {
        if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM payments
                                                                WHERE user_id = :userId
                                                                AND id = :id");
            
            $query->bindValue(":userId", $_SESSION['payments_user_id']);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM payments
                                                                WHERE user_id = :userId");
            
            $query->bindValue(":userId", $_SESSION['payments_user_id']);
            
            return $query->execute();
        }
    }
}