<?php
namespace App\Controller\Microservice\Api\Test;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;

class TestController extends Controller {
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
    *   name = "test_api",
    *   path = "/test_api",
    *	methods={"GET"}
    * )
    * @Template("@templateRoot/microservice/api/test/index.html.twig")
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
    *   name = "test_api_request",
    *   path = "/test_api_request",
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
            if (isset($_POST['event']) == true && $_POST['event'] == "test_api_request")
                $this->response['messages']['success'] = "Test api completed.";
            else
                $this->response['messages']['error'] = "Problem with request!";
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
    }
    
    // Functions private
}