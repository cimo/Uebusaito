<?php
namespace ReinventSoftware\UebusaitoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use ReinventSoftware\UebusaitoBundle\Classes\Ajax;
use ReinventSoftware\UebusaitoBundle\Classes\Captcha;

class RootController extends Controller {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $utility;
    private $query;
    private $ajax;
    private $captcha;
    
    private $response;
    
    // Properties
    
    // Functions public
    /**
     * @Template("UebusaitoBundle:render:index.html.twig")
     */
    public function indexAction($_locale, $urlCurrentPageId, $urlExtra) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->captcha = new Captcha($this->container, $this->entityManager);
        
        $this->utility->generateToken();
        $this->utility->configureCookie("PHPSESSID", 0, isset($_SERVER['HTTPS']), true);
        
        $this->response = Array();
        
        $this->response['captchaImage'] = $this->captcha->create(7);
        
        $event = isset($_POST['event']) == true ? $_POST['event'] : "";
        
        if ($event == "captchaImage") {
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
        
        $this->response['session']['token'] = isset($_SESSION['token']) == true ? $_SESSION['token'] : "";
        $this->response['session']['activity'] = $this->utilityPrivate->checkSessionOverTime();
        
        $this->response['path']['root'] = $this->utility->getPathRoot();
        $this->response['path']['srcBundle'] = $this->utility->getPathSrcBundle();
        $this->response['path']['webBundle'] = $this->utility->getPathWebBundle();
        
        $this->response['url']['root'] = $this->utility->getUrlRoot();
        $this->response['url']['rootFull'] = $this->utility->getUrlRootFull();
        $this->response['url']['webBundle'] = $this->utility->getUrlWebBundle();
        
        $this->response['modules']['header'] = $this->query->selectAllModulesDatabase(null, "header");
        $this->response['modules']['left'] = $this->query->selectAllModulesDatabase(null, "left");
        $this->response['modules']['center'] = $this->query->selectAllModulesDatabase(null, "center");
        $this->response['modules']['right'] = $this->query->selectAllModulesDatabase(null, "right");
        $this->response['modules']['footer'] = $this->query->selectAllModulesDatabase(null, "footer");
        
        $this->get("twig")->addGlobal("websiteName", $this->utility->getWebsiteName());
        $this->get("twig")->addGlobal("settings", $this->utility->getSettings());
        $this->get("twig")->addGlobal("captchaImage", $this->response['captchaImage']);
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    // Functions private
}