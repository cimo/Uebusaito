<?php
namespace App\Controller\MyPage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Form\PaymentSelectFormType;

class MyPagePaymentController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $helper;
    private $query;
    private $ajax;
    private $tableAndPagination;
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "myPage_payment_select",
    *   path = "/myPage_payment_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/my_page/myPage_payment_select.html.twig")
    */
    public function selectAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        $this->tableAndPagination = new TableAndPagination($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        $settingRow = $this->helper->getSettingRow();
        
        if ($settingRow['payment'] == true) {
            $this->session->set("paymentProfileId", 0);

            $paymentRows = $this->query->selectAllPaymentDatabase($this->getUser()->getId());

            $tableAndPagination = $this->tableAndPagination->request($paymentRows, 20, "payment", true);

            $this->response['values']['search'] = $tableAndPagination['search'];
            $this->response['values']['pagination'] = $tableAndPagination['pagination'];
            $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
            $this->response['values']['count'] = $tableAndPagination['count'];

            $form = $this->createForm(PaymentSelectFormType::class, null, Array(
                'validation_groups' => Array("payment_select"),
                'id' => array_reverse(array_column($paymentRows, "id", "transaction"), true)
            ));
            $form->handleRequest($request);

            if ($request->isMethod("POST") == true && $checkUserRole == true) {
                $id = 0;
                
                if ($this->isCsrfTokenValid("intention", $request->get("token")) == true)
                    $id = $request->get("id");
                else if ($form->isSubmitted() == true && $form->isValid() == true)
                    $id = $form->get("id")->getData();

                if ($request->get("event") != "refresh" && $request->get("event") != "tableAndPagination") {
                    $paymentEntity = $this->entityManager->getRepository("App\Entity\Payment")->find($id);

                    if ($paymentEntity != null) {
                        $this->session->set("paymentProfileId", $paymentEntity->getId());

                        $this->response['values']['payment'] = $paymentEntity;

                        $this->response['render'] = $this->renderView("@templateRoot/render/my_page/myPage_payment_profile.html.twig", Array(
                            'urlLocale' => $this->urlLocale,
                            'urlCurrentPageId' => $this->urlCurrentPageId,
                            'urlExtra' => $this->urlExtra,
                            'response' => $this->response
                        ));
                    }
                    else {
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("myPagePaymentController_1");
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
        else
            return new Response();
    }
    
    /**
    * @Route(
    *   name = "myPage_payment_delete",
    *   path = "/myPage_payment_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/my_page/myPage_payment_delete.html.twig")
    */
    public function deleteAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        $settingRow = $this->helper->getSettingRow();
        
        if ($settingRow['payment'] == true) {
            if ($request->isMethod("POST") == true && $checkUserRole == true) {
                if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                    if ($request->get("event") == "delete") {
                        $id = $request->get("id") == null ? $this->session->get("paymentProfileId") : $request->get("id");

                        $paymentDatabase = $this->query->updatePaymentDatabase("deleteOne", $this->session->get("paymentUserId"), $id);

                        if ($paymentDatabase == true) {
                            $this->response['values']['id'] = $id;

                            $this->response['messages']['success'] = $this->helper->getTranslator()->trans("myPagePaymentController_2");
                        }
                    }
                    else if ($request->get("event") == "deleteAll") {
                        $paymentDatabase = $this->query->updatePaymentDatabase("deleteAll", $this->session->get("paymentUserId"));

                        if ($paymentDatabase == true)
                            $this->response['messages']['success'] = $this->helper->getTranslator()->trans("myPagePaymentController_3");
                    }
                    else
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("myPagePaymentController_4");

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
        else
            return new Response();
    }
    
    // Functions private
    private function createListHtml($elements) {
        $listHtml = "";
        
        foreach ($elements as $key => $value) {
            $listHtml .= "<tr>
                <td class=\"id_column\">
                    {$value['id']}
                </td>
                <td class=\"checkbox_column\">
                    <div class=\"mdc-checkbox\">
                        <input class=\"mdc-checkbox__native-control\" type=\"checkbox\"/>
                        <div class=\"mdc-checkbox__background\">
                            <svg class=\"mdc-checkbox__checkmark\" viewBox=\"0 0 24 24\">
                                <path class=\"mdc-checkbox__checkmark-path\" fill=\"none\" stroke=\"white\" d=\"M1.73,12.91 8.1,19.28 22.79,4.59\"/>
                            </svg>
                            <div class=\"mdc-checkbox__mixedmark\"></div>
                        </div>
                    </div>
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
                <td>
                    <button class=\"mdc-fab mdc-fab--mini myPage_payment_delete icon_warning\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
}