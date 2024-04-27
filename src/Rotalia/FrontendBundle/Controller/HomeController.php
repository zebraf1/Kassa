<?php

namespace Rotalia\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HomeController extends AbstractController
{
    /**
     * Frontend bundle all files
     *
     * @param $fname
     * @param $extension
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function indexAction($fname, $extension): BinaryFileResponse
    {
        // Create a response.
        $path = $this->getPath($fname, $extension);

        if (file_exists($path)) {
            $response = new BinaryFileResponse($path);

            switch ($extension) {
                case 'html':
                    $response->headers->set('Content-Type', 'text/html');
                    break;
                case 'css':
                    $response->headers->set('Content-Type', 'text/css');
                    break;
                case 'js':
                    $response->headers->set('Content-Type', 'application/javascript');
                    // Service has to be reloaded every time
                    if((strpos($fname, 'service') !== false)) {
                        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
                    }
                    break;
                case 'png':
                    $response->headers->set('Content-Type', 'image/png');
                    break;
                case 'ico':
                    $response->headers->set('Content-Type', 'image/x-icon');
                    break;
                case 'json':
                    $response->headers->set('Content-Type', 'application/json');
                    break;
            }

            return $response;

        } else {
            throw new NotFoundHttpException($path);
        }
    }

    private function getPath($fname, $extension): string
    {
        return __DIR__."/../Resources/source/build/default/$fname.$extension";
    }
}
