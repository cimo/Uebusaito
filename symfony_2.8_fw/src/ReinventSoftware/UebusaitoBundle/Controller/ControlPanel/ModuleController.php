<?php
namespace ReinventSoftware\UebusaitoBundle\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Entity\Module;

use ReinventSoftware\UebusaitoBundle\Form\ModulesDragFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\ModulesDragModel;

use ReinventSoftware\UebusaitoBundle\Form\ModuleFormType;

use ReinventSoftware\UebusaitoBundle\Form\ModulesSelectionFormType;
use ReinventSoftware\UebusaitoBundle\Form\Model\ModulesSelectionModel;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Table;

class ModuleController extends Controller {
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
     * @Template("UebusaitoBundle:render:control_panel/modules_drag.html.twig")
     */
    public function dragAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        // Create form
        $modulesDragFormType = new ModulesDragFormType();
        $form = $this->createForm($modulesDragFormType, new ModulesDragModel(), Array(
            'validation_groups' => Array(
                'modules_drag'
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
                    $sortHeaderExplode = explode(",", $form->get("sortHeader")->getData());
                    $sortLeftExplode = explode(",", $form->get("sortLeft")->getData());
                    $sortCenterExplode = explode(",", $form->get("sortCenter")->getData());
                    $sortRightExplode = explode(",", $form->get("sortRight")->getData());

                    if (count($sortHeaderExplode) > 0) {
                        foreach ($sortHeaderExplode as $key => $value)
                            $this->modulesInDatabase("sort", $value, $key, "header");
                    }

                    if (count($sortLeftExplode) > 0) {
                        foreach ($sortLeftExplode as $key => $value)
                            $this->modulesInDatabase("sort", $value, $key, "left");
                    }

                    if (count($sortCenterExplode) > 0) {
                        foreach ($sortCenterExplode as $key => $value)
                            $this->modulesInDatabase("sort", $value, $key, "center");
                    }

                    if (count($sortRightExplode) > 0) {
                        foreach ($sortRightExplode as $key => $value)
                            $this->modulesInDatabase("sort", $value, $key, "right");
                    }

                    $this->response['messages']['success'] = $this->translator->trans("moduleController_1");
                }
                else {
                    $this->response['errors'] = $this->ajax->errors($form);
                    $this->response['messages']['error'] = $this->translator->trans("moduleController_2");
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
     * @Template("UebusaitoBundle:render:control_panel/module_creation.html.twig")
     */
    public function creationAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        $module = new Module();
        
        // Create form
        $moduleFormType = new ModuleFormType();
        $form = $this->createForm($moduleFormType, $module, Array(
            'validation_groups' => Array(
                'module_creation'
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
                    if ($form->get("sort")->getData() == "" && $form->get("active")->getData() == "") {
                        $moduleRows = $this->utility->getQuery()->selectAllModulesFromDatabase(null, $form->get("position")->getData());
                        
                        $form->getData()->setSort(count($moduleRows));
                        $form->getData()->setActive(false);
                    }
                    
                    // Insert in database
                    $this->entityManager->persist($module);
                    $this->entityManager->flush();

                    $this->response['messages']['success'] = $this->translator->trans("moduleController_3");
                }
                else {
                    $this->response['errors'] = $this->ajax->errors($form);
                    $this->response['messages']['error'] = $this->translator->trans("moduleController_4");
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
     * @Template("UebusaitoBundle:render:control_panel/modules_selection.html.twig")
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
        
        // Create form
        $modulesSelectionFormType = new ModulesSelectionFormType($this->utility);
        $form = $this->createForm($modulesSelectionFormType, new ModulesSelectionModel(), Array(
            'validation_groups' => Array(
                'modules_selection'
            )
        ));
        
        $moduleRows = $this->utility->getQuery()->selectAllModulesFromDatabase();
        
        $tableResult = $this->table->request($moduleRows, 20, "module", true, true);
        
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
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/modules_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                    
                    $this->response['render'] = $render;
                }
                else {
                    $this->response['errors'] = $this->ajax->errors($form);
                    $this->response['messages']['error'] = $this->translator->trans("moduleController_5");
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
     * @Template("UebusaitoBundle:render:control_panel/module_profile.html.twig")
     */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        $module = $this->entityManager->getRepository("UebusaitoBundle:Module")->find($this->urlExtra);
        
        // Create form
        $moduleFormType = new ModuleFormType();
        $form = $this->createForm($moduleFormType, $module, Array(
            'validation_groups' => Array(
                'module_profile'
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
                    $sortExplode = Array();
                    
                    if ($form->get("position")->getData() == "" && $form->get("sort")->getData() == "") {
                        $moduleRow = $this->utility->getQuery()->selectModuleFromDatabase($this->urlExtra);
                        
                        $form->getData()->setPosition($moduleRow['position']);
                        $form->getData()->setSort($moduleRow['sort']);
                    }
                    else {
                        $formDataSort = $form->get("sort")->getData();
                        
                        if ($formDataSort != null)
                            $sortExplode = explode(",", $formDataSort);
                    }
                    
                    // Insert in database
                    $this->entityManager->persist($module);
                    $this->entityManager->flush();
                    
                    if (count($sortExplode) > 0) {
                        foreach ($sortExplode as $key => $value)
                            $this->modulesInDatabase("sort", $value, $key);
                    }
                    
                    $this->response['messages']['success'] = $this->translator->trans("moduleController_6");
                }
                else {
                    $this->response['errors'] = $this->ajax->errors($form);
                    $this->response['messages']['error'] = $this->translator->trans("moduleController_7");
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
     * @Template("UebusaitoBundle:render:control_panel/module_profile_sort.html.twig")
     */
    public function profileSortAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->requestStack = $this->get("request_stack")->getCurrentRequest();
        $this->translator = $this->get("translator");
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->translator);
        
        $this->response = Array();
        
        // Request post
        if ($this->requestStack->getMethod() == "POST") {
            $sessionActivity = $this->utility->checkSessionOverTime($this->container, $this->requestStack);
            
            if ($sessionActivity != "")
                $this->response['session']['activity'] = $sessionActivity;
            else {
                // Check token
                if (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token']) {
                    $this->response['modules']['sort'] = $this->utility->getQuery()->selectAllModulesFromDatabase($this->requestStack->request->get("id"), $this->requestStack->request->get("position"));
                    
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/module_profile_sort.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                    
                    $this->response['render'] = $render;
                }
                else
                    $this->response['messages']['error'] = $this->translator->trans("moduleController_8");
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
    
    /**
     * @Template("UebusaitoBundle:render:control_panel/module_deletion.html.twig")
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
        
        $moduleRows = $this->utility->getQuery()->selectAllModulesFromDatabase();
        
        $tableResult = $this->table->request($moduleRows, 20, "module", true, true);
        
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
                    if ($this->requestStack->request->get("id") > 4) {
                        $module = $this->entityManager->getRepository("UebusaitoBundle:Module")->find($this->requestStack->request->get("id"));

                        // Remove from database
                        $this->entityManager->remove($module);
                        $this->entityManager->flush();
                    }
                    
                    $this->response['messages']['success'] = $this->translator->trans("moduleController_9");
                }
                else if (isset($_SESSION['token']) == true && $this->requestStack->request->get("token") == $_SESSION['token'] && $this->requestStack->request->get("event") == "deleteAll") {
                    // Remove from database
                    $this->modulesInDatabase("deleteAll");
                    
                    $render = $this->renderView("UebusaitoBundle::render/control_panel/modules_selection_desktop.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                    
                    $this->response['render'] = $render;

                    $this->response['messages']['success'] = $this->translator->trans("moduleController_10");
                }
                else
                    $this->response['messages']['error'] = $this->translator->trans("moduleController_11");
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
        $module = $this->entityManager->getRepository("UebusaitoBundle:Module")->find($id);
        
        if ($module != null) {
            $this->response['modules']['position'] = $module->getPosition();
            $this->response['modules']['sort'] = $this->utility->getQuery()->selectAllModulesFromDatabase(null, $module->getPosition());
            
            // Create form
            $moduleFormType = new ModuleFormType();
            $formModuleProfile = $this->createForm($moduleFormType, $module, Array(
                'validation_groups' => Array(
                    'module_profile'
                )
            ));
            
            $render = $this->renderView("UebusaitoBundle::render/control_panel/module_profile.html.twig", Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $module->getId(),
                'response' => $this->response,
                'form' => $formModuleProfile->createView()
            ));
            
            $this->response['render'] = $render;
        }
        else
            $this->response['messages']['error'] = $this->translator->trans("moduleController_5");
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
                    {$value['name']}
                </td>
                <td>
                    {$value['file_name']}
                </td>
                <td>";
                    if ($value['active'] == 0)
                        $this->listHtml .= $this->translator->trans("moduleController_12");
                    else
                        $this->listHtml .= $this->translator->trans("moduleController_13");
                $this->listHtml .= "</td>
                <td class=\"horizontal_center\">";
                    if ($value['id'] > 5)
                        $this->listHtml .= "<button class=\"cp_module_deletion btn btn-danger\"><i class=\"fa fa-remove\"></i></button>
                </td>
            </tr>";
        }
    }
    
    private function modulesInDatabase($type, $id = null, $sort = null, $position = null) {
        $connection = $this->entityManager->getConnection();
        
        if ($type == "deleteAll") {
            $query = $connection->prepare("DELETE FROM modules
                                            WHERE id > :idExclude");

            $query->bindValue(":idExclude", "4");

            $query->execute();
        }
        else {
            if ($position == null) {
                $query = $connection->prepare("UPDATE modules
                                                SET sort = :sort
                                                WHERE id = :id");
            }
            else {
                $query = $connection->prepare("UPDATE modules
                                                SET position = :position,
                                                    sort = :sort
                                                WHERE id = :id");
                
                $query->bindValue(":position", $position);
            }
            
            $query->bindValue(":sort", $sort);
            $query->bindValue(":id", $id);
            
            $query->execute();
        }
    }
}