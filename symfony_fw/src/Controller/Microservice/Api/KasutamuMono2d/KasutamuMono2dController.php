<?php
namespace App\Controller\Microservice\Api\KasutamuMono2d;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;

class KasutamuMono2dController extends Controller {
    // Vars
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $ajax;
    private $query;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "kasutamu_mono_2d_api_render",
    *   path = "/kasutamu_mono_2d_api_render",
    *	methods={"GET"}
    * )
    * @Template("@templateRoot/microservice/api/kasutamu_mono_2d/index.html.twig")
    */
    public function renderAction(Request $request) {
        header("Access-Control-Allow-Origin: *");
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        
        // Logic
        if ($request->isMethod("GET") == true) {
            return Array(
                'response' => $this->response
            );
        }
    }
    
    /**
    * @Route(
    *   name = "kasutamu_mono_2d_api_preview",
    *   path = "/kasutamu_mono_2d_api_preview",
    *	methods={"POST"}
    * )
    */
    public function previewAction(Request $request) {
        header("Access-Control-Allow-Origin: *");
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->ajax = new Ajax($this->container, $this->entityManager);
        $this->query = $this->utility->getQuery();
        
        // Logic
        if ($request->isMethod("POST") == true) {
            if (isset($_POST['imageBase64']) == true) {
                $path = "{$this->utility->getPathWeb()}/microservice/api/kasutamu_mono_2d/preview_customization";
                
                if (file_exists($path) == false)
                    mkdir($path, 0777, true);
                
                $imageBase64 = $_POST['imageBase64'];
                $imageBase64 = str_replace("data:image/png;base64,", "", $imageBase64);
                $imageBase64 = str_replace(" ", "+", $imageBase64);
                
                $uniqid = uniqid();
                
                $content = file_put_contents("$path/$uniqid.jpg", base64_decode($imageBase64));
                
                $this->response['values']['id'] = $uniqid;
                
                if ($content == true)
                    $this->response['messages']['success'] = "File saved.";
                else
                    $this->response['messages']['error'] = "Unable to save the file!";
            }
            else
                $this->response['messages']['error'] = "Problem with request!";
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
    }
    
    // Functions private
}