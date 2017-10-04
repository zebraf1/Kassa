<?php

namespace Rotalia\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HomeController extends Controller
{
    /**
     * Frontend bundle all files
     *
     * @param $fname
     * @param $extension
     * @return BinaryFileResponse
     */
    public function indexAction($fname, $extension)
    {

        // Create a response.
        $path = $this->getPath($fname, $extension);

        if (file_exists($path)) {

            // Check privileges.
            // This starts to fail with service worker.
            // Is it even necessary?
            /*
            if (preg_match("/elements\/kassa-ylevaade/i", $path)) {
                $this->denyAccessUnlessGranted('ROLE_USER');
            }

            if (preg_match("/elements\/kassa-admin/i", $path)) {
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
            }
            */

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
                    // Service worker wasn't reloaded
                    if((strpos($fname, 'service') !== false)) {
                        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
                    }
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
            throw new NotFoundHttpException("{$path}");
        }
    }

    private function getPath($fname, $extension) {

        $rootDir = $this->get('kernel')->getBundle('RotaliaFrontendBundle')->getPath();
        return "{$rootDir}/Resources/source/build/default/{$fname}.$extension";
    }

}
