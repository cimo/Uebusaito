<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Form\PaymentsUserSelectionFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\PaymentsUserSelectionModel;

use ReinventSoftware\UebusaitoBundle\Form\PaymentsSelectionFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\PaymentsSelectionModel;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Table;

class PaymentController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    private $requestStack;
    private $translator;
    
    private $utility;
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        if (isset($_SESSION['payments_user_id']) == false)
            $_SESSION['payments_user_id'] = $this->getUser()->getId();
        
        // Create form
        $paymentsUserSelectionFormType = new PaymentsUserSelectionFormType($this->utility, $_SESSION['payments_user_id']);
        $form = $this->createForm($paymentsUserSelectionFormType, new PaymentsUserSelectionModel(), Array(
            'validation_groups' => Array(
                'payments_user_selection'
            )
        ));
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);
                
                // Check form
                if ($form->isValid() == true) {
                    if ($form->get("userId")->getData() != null)
                        $_SESSION['payments_user_id'] = $form->get("userId")->getData();
                    
                    $this->response['messages']['success'] = "";
                }
                else {
                    $this->response['errors'] = $this->ajax->errors($form);
                    $this->response['messages']['error'] = $this->translator->trans("paymentController_1");
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        $this->table = new Table($this->utility);
        
        $this->listHtml = "";
        
        $this->response = Array();
        
        if (isset($_SESSION['payments_user_id']) == false)
            $_SESSION['payments_user_id'] = $this->getUser()->getId();
        
        // Create form
        $paymentsSelectionFormType = new PaymentsSelectionFormType($this->utility, $_SESSION['payments_user_id']);
        $form = $this->createForm($paymentsSelectionFormType, new PaymentsSelectionModel(), Array(
            'validation_groups' => Array(
                'payments_selection'
            )
        ));
        
        $paymentRows = $this->utility->getQuery()->selectAllPaymentsFromDatabase($_SESSION['payments_user_id']);
        
        $tableResult = $this->table->request($paymentRows, 20, "payment", true, true);
        
        $this->listHtml($tableResult['list']);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml;
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $id = 0;
            
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                $form->handleRequest($this->requestStack);
                
                // Check form
                if ($form->isValid() == true) {
                    $id = $form->get("id")->getData();

                    $this->selectionResult($id);
                }
                else if (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $form->isValid() == false && $this->requestStack->request->get("event") == null) {
                    $id = $this->requestStack->request->get("id") == "" ? 0 : $this->requestStack->request->get("id");

                    $this->selectionResult($id);
                }
                else if (isset($_POST['searchWritten']) == true && isset($_POST['paginationCurrent']) == true || (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $this->requestStack->request->get("event") == "refresh")) {
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/payments_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));

                    $this->response['render'] = $render;
                }
                else {
                    $this->response['errors'] = $this->ajax->errors($form);
                    $this->response['messages']['error'] = $this->translator->trans("paymentController_2");
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
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        $this->table = new Table($this->utility);
        
        $this->listHtml = "";
        
        $this->response = Array();
        
        $paymentRows = $this->utility->getQuery()->selectAllPaymentsFromDatabase($_SESSION['payments_user_id']);
        
        $tableResult = $this->table->request($paymentRows, 20, "payment", true, true);
        
        $this->listHtml($tableResult['list']);
        
        $this->response['values']['search'] = $tableResult['search'];
        $this->response['values']['pagination'] = $tableResult['pagination'];
        $this->response['values']['list'] = $this->listHtml;
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                if (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $this->requestStack->request->get("event") == null) {
                    $id = $this->requestStack->request->get("id") == "" ? 0 : $this->requestStack->request->get("id");
                    
                    $payment = $this->entityManager->getRepository("UebusaitoBundle:Payment")->find($id);
                    
                    // Remove from database
                    $this->entityManager->remove($payment);
                    $this->entityManager->flush();
                    
                    $this->response['messages']['success'] = $this->translator->trans("paymentController_3");
                }
                else if (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $this->requestStack->request->get("event") == "deleteAll") {
                    // Remove from database
                    $this->paymentsInDatabase();
                    
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/payments_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                    
                    $this->response['render'] = $render;

                    $this->response['messages']['success'] = $this->translator->trans("paymentController_4");
                }
                else
                    $this->response['messages']['error'] = $this->translator->trans("paymentController_5");
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
            $this->response['messages']['error'] = $this->translator->trans("paymentController_2");
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
        $connection = $this->entityManager->getConnection();
        
        $query = $connection->prepare("DELETE FROM payments
                                        WHERE user_id = :userId");

        $query->bindValue(":userId", $_SESSION['payments_user_id']);

        $query->execute();
    }
}