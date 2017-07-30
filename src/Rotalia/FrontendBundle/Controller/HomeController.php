<?php

namespace Rotalia\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HomeController extends Controller
{

    private $feSource = "./bundles/rotaliafrontend/build/default";

    /**
     * Frontend bundle landing page.
     *
     * @return BinaryFileResponse
     */
    public function indexAction()
    {

        $file = "{$this->feSource}/index.html";

        if (file_exists($file)) {
            return new BinaryFileResponse($file);
        } else {
            throw new NotFoundHttpException("{$file}");
        }
    }

    /**
     * Frontend bundle all files
     *
     * @param $path
     * @param $extencion
     * @return BinaryFileResponse
     */
    public function commonAction($path, $extencion)
    {

        $file = "{$this->feSource}/{$path}.{$extencion}";

        if (file_exists($file)) {
            $response = new BinaryFileResponse($file);

            switch ($extencion) {
                case 'html':
                    $response->headers->set('Content-Type', 'text/html');
                    break;
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
