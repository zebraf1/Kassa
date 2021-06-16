<?php

namespace Rotalia\APIBundle\Controller;


use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\UserBundle\Model\User;
use Rotalia\UserBundle\Model\UserQuery;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices; // Used for API documentation

/**
 * Class AuthenticationController
 * @package Rotalia\APIBundle\Controller
 */
class AuthenticationController extends DefaultController
{
    /**
     * Check current session.
     * Return logged in member data (id, name, conventId, creditBalance, roles) or null.
     * Return current browser pointOfSale name or null.
     * Generate and return a CSRF token for the session when member is not logged in. This token is required upon login.
     *
     * @ApiDoc(
     *     statusCodes = {
     *          200 = "Returned when successful",
     *     },
     *     description="Fetch session state",
     *     section="Authentication"
     * )
     *
     * @param Request $request
     * @return JSendResponse
     */
    public function tokenAction(Request $request)
    {
        $member = $this->getMember();
        /** @var User $user */
        $user = $this->getUser();
        $pos = $this->getPos($request);

        $memberData = null;
        if ($member !== null) {
            $memberData = $member->getAjaxData(true);

            if ($user !== null) {
                $memberData['roles'] = $user->getRoles();
            }
        }

        $data = [
            'member' =>  $memberData,
            'pointOfSaleId' =>  $pos ? $pos->getId() : null,
            'csrfToken' => $member ? null : $this->getCSRFProvider()->generateCsrfToken($this->getTokenId())
        ];

        
        $response = JSendResponse::createSuccess($data);

        if ($pos === null && $hash = $request->cookies->get('pos_hash')) {
            // Delete cookie
            $response->headers->setCookie(new Cookie('pos_hash', 'deleted', 1));
        } elseif ($pos !== null) {
            // Refresh cookie lifetime
            $response->headers->setCookie(new Cookie('pos_hash', $pos->getHash(), new \DateTime('+1 year')));
        }

        return $response;
    }

    /**
     * Validate login token and given credentials. Create a new session for the given user and set PHPSESSID cookie
     * for further communication between the server.
     *
     * @ApiDoc(
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          400 = "Returned when CSRF token is missing or it is invalid",
     *          401 = "Returned when username or password provided is incorrect",
     *     },
     *     description="Create new session",
     *     section="Authentication",
     *     formType="POST",
     *     parameters={
     *          {"name"="csrfToken","dataType"="string","required"=true},
     *          {"name"="username","dataType"="string","required"=true},
     *          {"name"="password","dataType"="string","required"=true},
     *          {"name"="rememberMe","dataType"="boolean","required"=false},
     *     }
     * )
     *
     * @param Request $request
     * @return JSendResponse
     */
    public function loginAction(Request $request)
    {
        // Validate CSRF token to avoid request forging
        $csrfToken = $request->request->get('csrfToken');

        if ($csrfToken == null) {
            return JSendResponse::createFail('Login token puudub', 400);
        }

        if (!$this->getCSRFProvider()->isCsrfTokenValid($this->getTokenId(), $csrfToken)) {
            return JSendResponse::createFail('Vigane login token, proovi uuesti', 400);
        }

        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $user = UserQuery::create()->findOneByUsername($username);
        if ($user === null) {
            return JSendResponse::createFail('Kasutajat ei leitud', 401);
        }

        $token = new UsernamePasswordToken($username, $password, "main", $user->getRoles());

        /** @var AuthenticationManagerInterface $authenticationManager */
        $authenticationManager = $this->get('security.authentication.manager');
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->get('security.token_storage');
        try {
            $newToken = $authenticationManager->authenticate($token);
            $tokenStorage->setToken($newToken);
        } catch (BadCredentialsException $e) {
            return JSendResponse::createFail('Vale kasutaja/parool', 401);
        } catch (AuthenticationException $e) {
            return JSendResponse::createFail('Logimine ebaõnnestus. '.$e->getMessage(), 401);
        }

        $response = JSendResponse::createSuccess('Autoriseerimine õnnestus');

        $rememberMe = $request->get('rememberMe', false);
        if ($rememberMe) {
            /** @var RememberMeServicesInterface $rememberMeService */

            $userProvider = $this->get('rotalia_user_provider');
            // TODO get config from services.yml
            $rememberMeService = new TokenBasedRememberMeServices([$userProvider], $this->getParameter('secret'), "main", [
                    'name' => 'REMEMBERME',
                    'lifetime' => 31536000,
                    'path' => '/',
                    'domain' => null,
                    'secure' => false,
                    'httponly' => true,
                    'always_remember_me' => false,
                    'remember_me_parameter' => 'rememberMe',
            ]);
            $rememberMeToken = new RememberMeToken($user, "main", $this->getParameter('secret'));
            $rememberMeService->loginSuccess($request, $response, $rememberMeToken);
            $tokenStorage->setToken($rememberMeToken);
        }

        return $response;
    }

    /**
     * Invalidate the current session and destroy any authentication data
     *
     * @ApiDoc(
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          500 = "Returned when session invalidation fails",
     *     },
     *     description="Logout",
     *     section="Authentication",
     *     formType="POST"
     * )
     *
     * @param Request $request
     * @return JSendResponse
     */
    public function logoutAction(Request $request)
    {
        // Logout user
        $token = $this->get('security.token_storage')->getToken();
        $this->get('security.token_storage')->setToken(null);

        // Invalidate session
        if ($request->getSession()->invalidate()) {
            $response = JSendResponse::createSuccess('Välja logitud');

            // Delete rememberMe cookie upon logout
            if ($token instanceof RememberMeToken) {
                $userProvider = $this->get('rotalia_user_provider');
                // TODO get config from services.yml
                $rememberMeService = new TokenBasedRememberMeServices([$userProvider], $this->getParameter('secret'), "main", [
                    'name' => 'REMEMBERME',
                    'lifetime' => 31536000,
                    'path' => '/',
                    'domain' => null,
                    'secure' => false,
                    'httponly' => true,
                    'always_remember_me' => false,
                    'remember_me_parameter' => 'rememberMe',
                ]);

                $rememberMeService->logout($request, $response, $token);
            }

            return $response;
        } else {
            return JSendResponse::createError('Välja logimine ebaõnnestus', 500);
        }
    }

    /**
     * CRSF token ID for current controller
     *
     * @return string
     */
    protected function getTokenId()
    {
        return 'restApiCsrfTokenId';
    }
}
