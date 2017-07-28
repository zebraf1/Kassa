<?php

namespace Rotalia\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HomeController extends Controller
{
    /**
     * Frontend bundle landing page
     *
     * @param $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {


        return $this->render('RotaliaFrontendBundle:Default:index.html', []);
    }

}
