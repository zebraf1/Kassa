<?php

namespace Rotalia\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HomeController extends Controller
{

    private $feSource = "./bundles/rotaliafrontend/build/es5-unbundled";

    /**
     * Frontend bundle landing page.
     *
     * @return Response
     */
    public function indexAction()
    {

        $file = "{$this->feSource}/index.html";

        if (file_exists($file)) {
            return new Response(file_get_contents($file));
        } else {
            throw new NotFoundHttpException("{$file}");
        }
    }

    /**
     * Frontend bundle all files
     *
     * @param $path
     * @param $extencion
     * @return Response
     */
    public function commonAction($path, $extencion)
    {

        $file = "{$this->feSource}/{$path}.{$extencion}";

        if (file_exists($file)) {
            $response = new Response(file_get_contents($file));

            switch ($extencion) {
                case 'css':
                    $response->headers->set('Content-Type', 'text/css');
                    break;
                case 'js':
                    $response->headers->set('Content-Type', 'application/javascript');
                    break;
                case 'png':
                    $response->headers->set('Content-Type', 'image/png');
                    break;
                case 'json':
                    $response->headers->set('Content-Type', 'application/json');
                    break;
            }

            return $response;

        } else {
            throw new NotFoundHttpException("{$file}");
        }
    }

}
