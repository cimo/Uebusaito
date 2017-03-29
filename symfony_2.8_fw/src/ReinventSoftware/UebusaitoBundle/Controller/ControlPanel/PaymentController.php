<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Table;

use ReinventSoftware\UebusaitoBundle\Form\PaymentsUserSelectionFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\PaymentsUserSelectionModel;

use ReinventSoftware\UebusaitoBundle\Form\PaymentsSelectionFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\PaymentsSelectionModel;

class PaymentController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $utility;
    private $query;
    private $ajax;
    private $table;
    
    private $listHtml;
    
    private $response;
    
    // Properties
    
    // Functions public
     /**
     * @Template("UebusaitoBundle:render:control_panel/payments_user_selection.html.twig")
     */
    public function userSelectionAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = new Query($this->connection);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->response = Array();
        
        if (isset($_SESSION['payments_user_id']) == false)
            $_SESSION['payments_user_id'] = $this->getUser()->getId();
        
        // Create form
        $paymentsUserSelectionFormType = new PaymentsUserSelectionFormType($this->container, $this->entityManager, $this->getUser()->getId());
        $form = $this->createForm($paymentsUserSelectionFormType, new PaymentsUserSelectionModel(), Array(
            'validation_groups' => Array(
                'payments_user_selection'
            )
        ));
        
        $userRoleLevelRow = $this->query->selectUserRoleLevelFromDatabase($this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && in_array("ROLE_ADMIN", $userRoleLevelRow) == true && in_array("ROLE_MODERATOR", $userRoleLevelRow) == true) {
            $sessionActivity = $this->utility->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->utility->getRequestStack());
                
                // Check form
                if ($form->isValid() == true) {
                    if ($form->get("userId")->getData() != null)
                        $_SESSION['payments_user_id'] = $form->get("userId")->getData();
                    
                    $this->response['messages']['success'] = "";
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("paymentController_1");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
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
     * @Template("UebusaitoBundle:render:control_panel/payments_selection.html.twig")
     */
    public function selectionAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = new Query($this->connection);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->table = new Table($this->container, $this->entityManager);
        
        $this->listHtml = "";
        
        $this->response = Array();
        
        if (isset($_SESSION['payments_user_id']) == false)
            $_SESSION['payments_user_id'] = $this->getUser()->getId();
        
        // Create form
        $paymentsSelectionFormType = new PaymentsSelectionFormType($this->container, $this->entityManager, $this->getUser()->getId());
        $form = $this->createForm($paymentsSelectionFormType, new PaymentsSelectionModel(), Array(
            'validation_groups' => Array(
                'payments_selection'
            )
        ));
        
        // Pagination
        $paymentRows = $this->query->selectAllPaymentsFromDatabase($_SESSION['payments_user_id']);
        
        $tableResult = $this->table->request($paymentRows, 20, "payment", true, true);
        
        $this->listHtml($tableResult['list']);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml;
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST") {
            $id = 0;
            
            $sessionActivity = $this->utility->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->utility->getRequestStack());
                
                // Check form
                if ($form->isValid() == true) {
                    $id = $form->get("id")->getData();

                    $this->selectionResult($id);
                }
                else if ($form->isValid() == false && isset($_SESSION['token']) == true && $this->utility->getRequestStack()->request->get("token") == $_SESSION['token'] && $this->utility->getRequestStack()->request->get("event") == null) {
                    $id = $this->utility->getRequestStack()->request->get("id") == "" ? 0 : $this->utility->getRequestStack()->request->get("id");

                    $this->selectionResult($id);
                }
                else if (isset($_POST['searchWritten']) == true && isset($_POST['paginationCurrent']) == true ||
                            (isset($_SESSION['token']) == true && $this->utility->getRequestStack()->request->get("token") == $_SESSION['token'] && $this->utility->getRequestStack()->request->get("event") == "refresh")) {
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/payments_selection_desktop.html.twig", Array(
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
     * @Template("UebusaitoBundle:render:control_panel/payment_deletion.html.twig")
     */
    public function deletionAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = new Query($this->connection);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->table = new Table($this->container, $this->entityManager);
        
        $this->listHtml = "";
        
        $this->response = Array();
        
        $paymentRows = $this->query->selectAllPaymentsFromDatabase($_SESSION['payments_user_id']);
        
        $tableResult = $this->table->request($paymentRows, 20, "payment", true, true);
        
        $this->listHtml($tableResult['list']);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml;
        
        $userRoleLevelRow = $this->query->selectUserRoleLevelFromDatabase($this->getUser()->getRoleId());
        
        // Request post
        if ($this->utility->getRequestStack()->getMethod() == "POST" && in_array("ROLE_ADMIN", $userRoleLevelRow) == true) {
            $sessionActivity = $this->utility->checkSessionOverTime();
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                if (isset($_SESSION['token']) == true && $this->utility->getRequestStack()->request->get("token") == $_SESSION['token'] && $this->utility->getRequestStack()->request->get("event") == null) {
                    $id = $this->utility->getRequestStack()->request->get("id") == "" ? 0 : $this->utility->getRequestStack()->request->get("id");
                    
                    $payment = $this->entityManager->getRepository("UebusaitoBundle:Payment")->find($id);
                    
                    // Remove from database
                    $this->entityManager->remove($payment);
                    $this->entityManager->flush();
                    
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("paymentController_3");
                }
                else if (isset($_SESSION['token']) == true && $this->utility->getRequestStack()->request->get("token") == $_SESSION['token'] && $this->utility->getRequestStack()->request->get("event") == "deleteAll") {
                    // Remove from database
                    $this->paymentsInDatabase();
                    
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/payments_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                    
                    $this->response['render'] = $render;

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("paymentController_4");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("paymentController_5");
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
            'response' => $this->response
        );
    }
    
    // Functions private
    private function selectionResult($id) {
        $payment = $this->entityManager->getRepository("UebusaitoBundle:Payment")->find($id);
        
        if ($payment != null && $payment->getUserId() == $_SESSION['payments_user_id']) {
            $this->response['values']['payment'] = $payment;
            
            $render = $this->renderView("UebusaitoBundle::render/control_panel/payment_profile.html.twig", Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $payment->getId(),
                'response' => $this->response
            ));

            $this->response['render'] = $render;
        }
        else
            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("paymentController_2");
    }
    
    private function listHtml($tableResult) {
        foreach ($tableResult as $key => $value) {
            $this->listHtml .= "<tr>
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
                    <button class=\"cp_payment_deletion btn btn-danger\"><i class=\"fa fa-remove\"></i></button>
                </td>
            </tr>";
        }
    }
    
    private function paymentsInDatabase() {
        $query = $this->utility->getConnection()->prepare("DELETE FROM payments
                                                            WHERE user_id = :userId");

        $query->bindValue(":userId", $_SESSION['payments_user_id']);

        $query->execute();
    }
}