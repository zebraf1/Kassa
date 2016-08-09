<?php

namespace Rotalia\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HomeController extends Controller
{
    /**
     * Frontend bundle landing page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('RotaliaFrontendBundle:Default:index.html.twig', []);
    }
    
    /**
     * Rerouting service worker file to root directory
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showServiceWorkerAction()
    {
		$filepath = realpath($this->get('kernel')->getRootDir()).'/../web/bundles/rotaliafrontend/service-worker.js';
		$response = new BinaryFileResponse($filepath);
		$response->headers->set('Content-Type', 'text/javascript');
        return $response;
    }
}
