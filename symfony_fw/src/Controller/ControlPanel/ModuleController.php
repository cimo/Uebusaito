<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Entity\Module;

use App\Form\ModuleFormType;
use App\Form\ModuleSelectionFormType;

class ModuleController extends Controller {
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
    *   name = "cp_module_creation",
    *   path = "/cp_module_creation/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/module_creation.html.twig")
    */
    public function creationAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $moduleEntity = new Module();
        
        $_SESSION['moduleProfileId'] = 0;
        
        $form = $this->createForm(ModuleFormType::class, $moduleEntity, Array(
            'validation_groups' => Array('module_creation')
        ));
        $form->handleRequest($request);
        
        $moduleRows = Array();
        
        $this->response['values']['moduleSortListHtml'] = $this->utility->createModuleSortListHtml($moduleRows);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isValid() == true) {
                $moduleEntity->setActive(false);

                // Insert in database
                $this->entityManager->persist($moduleEntity);
                $this->entityManager->flush();
                
                $this->updateRankInColumnDatabase($form->get("rankColumnSort")->getData(), $moduleEntity->getId());

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("moduleController_1");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_2");
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
    *   name = "cp_module_selection",
    *   path = "/cp_module_selection/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/module_selection.html.twig")
    */
    public function selectionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->tableAndPagination = new TableAndPagination($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $_SESSION['moduleProfileId'] = 0;
        
        $moduleRows = $this->query->selectAllModuleDatabase();
        
        $tableAndPagination = $this->tableAndPagination->request($moduleRows, 20, "module", true, true);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(ModuleSelectionFormType::class, null, Array(
            'validation_groups' => Array('module_selection'),
            'choicesId' => array_reverse(array_column($moduleRows, "id", "name"), true)
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
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
    *   name = "cp_module_profile_result",
    *   path = "/cp_module_profile_result/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/module_profile.html.twig")
    */
    public function profileResultAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if (($this->isCsrfTokenValid("intention", $request->get("token")) == true
                    || $this->isCsrfTokenValid("intention", $request->get("form_module_selection")['_token']) == true)) {
                $id = 0;

                if (empty($request->get("id")) == false)
                    $id = $request->get("id");
                else if (empty($request->get("form_module_selection")['id']) == false)
                    $id = $request->get("form_module_selection")['id'];

                $moduleEntity = $this->entityManager->getRepository("App\Entity\Module")->find($id);

                if ($moduleEntity != null) {
                    $_SESSION['moduleProfileId'] = $id;

                    $form = $this->createForm(ModuleFormType::class, $moduleEntity, Array(
                        'validation_groups' => Array('module_profile')
                    ));
                    $form->handleRequest($request);
                    
                    $rows = array_column($this->query->selectAllModuleDatabase(null, $form->get("position")->getData()), "name", "id");
                    
                    $this->response['values']['moduleSortListHtml'] = $this->utility->createModuleSortListHtml($rows);
                    $this->response['values']['id'] = $_SESSION['moduleProfileId'];

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/module_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $form->createView()
                    ));
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_3");
            }
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
    *   name = "cp_module_profile_sort",
    *   path = "/cp_module_profile_sort/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/module_profile.html.twig")
    */
    public function profileSortAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("position") != "") {
                    $rows = array_column($this->query->selectAllModuleDatabase(null, $request->get("position")), "name", "id");

                    if ($_SESSION['moduleProfileId'] > 0) {
                        $moduleEntity = $this->entityManager->getRepository("App\Entity\Module")->find($_SESSION['moduleProfileId']);

                        $rows[$moduleEntity->getId()] = $moduleEntity->getName();
                    }
                }
                else
                    $rows = Array();
                
                $this->response['values']['moduleSortListHtml'] = $this->utility->createModuleSortListHtml($rows);
            }
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
    *   name = "cp_module_profile_save",
    *   path = "/cp_module_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/module_profile.html.twig")
    */
    public function profileSaveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser()->getRoleUserId());
        
        // Logic
        $moduleEntity = $this->entityManager->getRepository("App\Entity\Module")->find($_SESSION['moduleProfileId']);
        
        $form = $this->createForm(ModuleFormType::class, $moduleEntity, Array(
            'validation_groups' => Array('module_profile')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isValid() == true) {
                // Update in database
                $this->entityManager->persist($moduleEntity);
                $this->entityManager->flush();
                
                $this->updateRankInColumnDatabase($form->get("rankColumnSort")->getData(), $moduleEntity->getId());

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("moduleController_4");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_5");
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
    *   name = "cp_module_deletion",
    *   path = "/cp_module_deletion/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/module_deletion.html.twig")
    */
    public function deletionAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser()->getRoleUserId());
        
        // Logic
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $_SESSION['moduleProfileId'] : $request->get("id");

                    $moduleDatabase = $this->moduleDatabase("delete", $id, null, null);

                    if ($moduleDatabase == true) {
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("moduleController_6");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $moduleDatabase = $this->moduleDatabase("deleteAll", null, null, null);

                    if ($moduleDatabase == true)
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("moduleController_7");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("moduleController_8");

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
                    {$value['name']}
                </td>
                <td>
                    {$value['controller_name']}
                </td>
                <td>";
                    if ($value['active'] == 0)
                        $listHtml .= $this->utility->getTranslator()->trans("moduleController_9");
                    else
                        $listHtml .= $this->utility->getTranslator()->trans("moduleController_10");
                $listHtml .= "</td>
                <td class=\"horizontal_center\">";
                    if ($value['id'] > 2)
                        $listHtml .= "<button class=\"mdc-fab mdc-fab--mini cp_module_deletion\" type=\"button\" aria-label=\"Delete\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function updateRankInColumnDatabase($rankColumnSort, $moduleId) {
        $rankColumnSortExplode = explode(",", $rankColumnSort);
        array_pop($rankColumnSortExplode);
        
        foreach ($rankColumnSortExplode as $key => $value) {
            if ($value == "")
                $value = $moduleId;

            $query = $this->utility->getConnection()->prepare("UPDATE modules
                                                                SET rank_in_column = :rankInColumn
                                                                WHERE id = :id");

            $query->bindValue(":rankInColumn", $key + 1);
            $query->bindValue(":id", $value);

            $query->execute();
        }
    }
    
    private function moduleDatabase($type, $id, $rankInColumn, $position) {
        if ($type == "update") {
            $query = $this->utility->getConnection()->prepare("UPDATE modules
                                                                SET position = :position,
                                                                    rank_in_column = :rankInColumn
                                                                WHERE id = :id");
                
            $query->bindValue(":position", $position);
            $query->bindValue(":rankInColumn", $rankInColumn);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM modules
                                                                WHERE id > :idExclude
                                                                AND id = :id");

            $query->bindValue(":idExclude", 2);
            $query->bindValue(":id", $id);

            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM modules
                                                                WHERE id > :idExclude");

            $query->bindValue(":idExclude", 2);

            return $query->execute();
        }
    }
}