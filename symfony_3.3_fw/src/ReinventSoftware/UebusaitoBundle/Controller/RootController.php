<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\System\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UebusaitoUtility;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Captcha;

class RootController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $uebusaitoUtility;
    private $query;
    private $ajax;
    private $captcha;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "root_render",
    *   path = "/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = ".*"}
    * )
    * @Method({"GET"})
    * @Template("@UebusaitoBundleViews/render/index.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        $this->uebusaitoUtility = new UebusaitoUtility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->captcha = new Captcha($this->container, $this->entityManager);
        
        $this->urlLocale = $this->uebusaitoUtility->checkLanguage($request);
        
        $this->utility->generateToken();
        
        $this->utility->configureCookie(session_name(), 0, isset($_SERVER['HTTPS']), true);
        
        $this->response['captchaImage'] = $this->captcha->create(7);
        
        $event = isset($_POST['event']) == true ? $_POST['event'] : "";
        
        if ($event == "captchaImage") {
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
        
        $this->response['path']['documentRoot'] = $_SERVER['DOCUMENT_ROOT'];
        $this->response['path']['root'] = $this->utility->getPathRoot();
        $this->response['path']['srcBundle'] = $this->utility->getPathSrcBundle();
        $this->response['path']['webBundle'] = $this->utility->getPathWebBundle();
        
        $this->response['url']['root'] = $this->utility->getUrlRoot();
        
        $this->response['modules']['header'] = $this->query->selectAllModulesDatabase(null, "header");
        $this->response['modules']['left'] = $this->query->selectAllModulesDatabase(null, "left");
        $this->response['modules']['center'] = $this->query->selectAllModulesDatabase(null, "center");
        $this->response['modules']['right'] = $this->query->selectAllModulesDatabase(null, "right");
        $this->response['modules']['footer'] = $this->query->selectAllModulesDatabase(null, "footer");
        
        $this->utility->checkSessionOverTime($request, true);
        
        $this->get("twig")->addGlobal("php_session", $_SESSION);
        $this->get("twig")->addGlobal("websiteName", $this->utility->getWebsiteName());
        $this->get("twig")->addGlobal("settingRow", $this->query->selectSettingDatabase());
        $this->get("twig")->addGlobal("captchaImage", $this->response['captchaImage']);
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    // Function private
}
