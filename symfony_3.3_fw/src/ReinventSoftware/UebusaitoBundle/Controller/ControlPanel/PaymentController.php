<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Table;

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
    private $table;
    
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
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utilityPrivate->checkLanguage($request);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        if (isset($_SESSION['payments_user_id']) == false)
            $_SESSION['payments_user_id'] = $this->getUser()->getId();
        
        // Form
        $form = $this->createForm(PaymentsUserSelectionFormType::class, null, Array(
            'validation_groups' => Array('payments_user_selection'),
            'choicesId' => array_column($this->query->selectAllUsersDatabase(), "id", "username")
        ));
        $form->handleRequest($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($form->isValid() == true) {
                if ($form->get("userId")->getData() != null)
                    $_SESSION['payments_user_id'] = $form->get("userId")->getData();

                $this->response['messages']['success'] = "";
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("paymentController_1");
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
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->table = new Table($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utilityPrivate->checkLanguage($request);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        if (isset($_SESSION['payments_user_id']) == false)
            $_SESSION['payments_user_id'] = $this->getUser()->getId();
        
        // Pagination
        $paymentRows = $this->query->selectAllPaymentsDatabase($_SESSION['payments_user_id']);
        
        $tableResult = $this->table->request($paymentRows, 20, "payment", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->createListHtml($tableResult['list']);
        
        // Form
        $form = $this->createForm(PaymentsSelectionFormType::class, null, Array(
            'validation_groups' => Array('payments_selection'),
            'choicesId' => array_reverse(array_column($this->query->selectAllPaymentsDatabase($_SESSION['payments_user_id']), "id", "transaction"), true)
        ));
        $form->handleRequest($request);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            $id = 0;
            
            if ($form->isValid() == true) {
                $id = $form->get("id")->getData();

                $this->selectionResult($id);
            }
            else if ($request->get("event") == null && $this->utilityPrivate->checkToken($request) == true) {
                $id = $request->get("id") == "" ? 0 : $request->get("id");

                $this->selectionResult($id);
            }
            else if (($request->get("event") == "refresh" && $this->utilityPrivate->checkToken($request) == true) || $this->table->checkPost() == true) {
                $render = $this->renderView("@UebusaitoBundleViews/render/control_panel/payments_selection_desktop.html.twig", Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));

                $this->response['render'] = $render;
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("paymentController_2");
                $this->response['errors'] = $this->ajax->errors($form);
            }
            
            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $id,
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
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->table = new Table($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utilityPrivate->checkLanguage($request);
        
        $this->utilityPrivate->checkSessionOverTime($request);
        
        // Pagination
        $paymentRows = $this->query->selectAllPaymentsDatabase($_SESSION['payments_user_id']);
        
        $tableResult = $this->table->request($paymentRows, 20, "payment", true, true);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->createListHtml($tableResult['list']);
        
        $chekRoleLevel = $this->utilityPrivate->checkRoleLevel(Array("ROLE_ADMIN"), $this->getUser()->getRoleId());
        
        if ($request->isMethod("POST") == true && $chekRoleLevel == true) {
            if ($request->get("event") == "delete" && $this->utilityPrivate->checkToken($request) == true) {
                $paymentsDatabase = $this->paymentsDatabase("delete", $request->get("id"));

                if ($paymentsDatabase == true)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("paymentController_3");
            }
            else if ($request->get("event") == "deleteAll" && $this->utilityPrivate->checkToken($request) == true) {
                $paymentsDatabase = $this->paymentsDatabase("deleteAll", null);

                if ($paymentsDatabase == true) {
                    $render = $this->renderView("@UebusaitoBundleViews/render/control_panel/payments_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));

                    $this->response['render'] = $render;

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("paymentController_4");
                }
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
    private function selectionResult($id) {
        $paymentEntity = $this->entityManager->getRepository("UebusaitoBundle:Payment")->find($id);
        
        if ($paymentEntity != null && $paymentEntity->getUserId() == $_SESSION['payments_user_id']) {
            $this->response['values']['payment'] = $paymentEntity;
            
            $render = $this->renderView("@UebusaitoBundleViews/render/control_panel/payment_profile.html.twig", Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $paymentEntity->getId(),
                'response' => $this->response
            ));

            $this->response['render'] = $render;
        }
        else
            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("paymentController_2");
    }
    
    private function createListHtml($elements) {
        $listHtml = "";
        
        $count = 0;
        
        foreach ($elements as $key => $value) {
            $count ++;
            
            $listHtml .= "<tr>
                <td class=\"id_column\">
                    {$count}
                </td>
                <td class=\"display_none id_column_hide\">
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