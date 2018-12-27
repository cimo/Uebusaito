<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;

class ControlPanelController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "control_panel",
    *   path = "/control_panel/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"GET"}
    * )
    * @Template("@templateRoot/render/control_panel.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->urlLocale = $this->utility->checkLanguage($request);
        
        $this->utility->checkSessionOverTime($request);
        
        // Logic
        $_SESSION['currentPageId'] = $urlCurrentPageId;
        
        $this->response['path']['documentRoot'] = $_SERVER['DOCUMENT_ROOT'];
        $this->response['path']['root'] = $this->utility->getPathRoot();
        $this->response['path']['src'] = $this->utility->getPathSrc();
        $this->response['path']['web'] = $this->utility->getPathWeb();
        
        $this->response['url']['root'] = $this->utility->getUrlRoot();
        
        $this->response['modules']['left'] = $this->query->selectAllModuleDatabase(null, "left");
        $this->response['modules']['center'] = $this->query->selectAllModuleDatabase(null, "center");
        $this->response['modules']['right'] = $this->query->selectAllModuleDatabase(null, "right");
        
        $this->response['output']['phpinfo'] = $this->parsePhpinfo();
        
        $this->get("twig")->addGlobal("php_session", $_SESSION);
        $this->get("twig")->addGlobal("websiteName", $this->utility->getWebsiteName());
        $this->get("twig")->addGlobal("settingRow", $this->query->selectSettingDatabase());
        
        if ($this->container->get("session")->isStarted() == true) {
            $session = $request->getSession();
            $session->set("php_session", $_SESSION);
        }
        
        // Logic
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    // Functions private
    function parsePhpinfo() {
        ob_start();
        phpinfo();
        $contents = ob_get_contents();
        ob_end_clean();
        
        $contents = strip_tags($contents, "<h2><th><td>");
        $contents = preg_replace("/<th[^>]*>([^<]+)<\/th>/", '<info>\1</info>', $contents);
        $contents = preg_replace("/<td[^>]*>([^<]+)<\/td>/", '<info>\1</info>', $contents);
        
        $title = preg_split("/(<h2[^>]*>[^<]+<\/h2>)/", $contents, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        $rows = Array();
        $countTitle = count($title);
        
        $pA = "<info>([^<]+)<\/info>";
        $pB = "/$pA\s*$pA\s*$pA/";
        $pC = "/$pA\s*$pA/";
        
        for ($a = 1; $a < $countTitle; $a++) {
            preg_match("/<h2[^>]*>([^<]+)<\/h2>/", $title[$a], $matchesA);
                    
            if (count($matchesA) > 0) {
                $name = trim($matchesA[1]);
                $titleExplode = explode("\n", $title[$a + 1]);
                
                foreach ($titleExplode as $key => $value) {
                    preg_match($pB, $value, $matchesB);
                    preg_match($pC, $value, $matchesC);
                            
                    if (count($matchesB) > 0)
                        $rows[$name][trim($matchesB[1])] = array(trim($matchesB[2]), trim($matchesB[3]));
                    else if (count($matchesC) > 0)
                        $rows[$name][trim($matchesC[1])] = trim($matchesC[2]);
                }
            }
        }
        
        return $rows;
    }
}