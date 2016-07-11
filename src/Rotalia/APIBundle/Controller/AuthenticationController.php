<?php

namespace Rotalia\APIBundle\Controller;


use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\UserBundle\Model\UserQuery;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Nelmio\ApiDocBundle\Annotation\ApiDoc; // Used for API documentation

/**
 * Class AuthenticationController
 * @package Rotalia\APIBundle\Controller
 */
class AuthenticationController extends DefaultController
{
    /**
     * Check current session.
     * Return logged in member name or null.
     * Return current browser pointOfSale name or null.
     * Generate and return a CSRF token for the session when member is not logged in. This token is required upon login.
     *
     * @ApiDoc(
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
        $pos = $this->getPos($request);

        $data = [
            'member' =>  $member ? $member->getAjaxName() : null,
            'pointOfSale' =>  $pos ? $pos->getName() : null,
            'csrfToken' => $member ? null : $this->getCSRFProvider()->generateCsrfToken($this->getTokenId())
        ];

        
        return JSendResponse::createSuccess($data);
    }

    /**
     * Validate login token and given credentials. Create a new session for the given user and set PHPSESSID cookie
     * for further communication between the server.
     *
     * @ApiDoc(
     *     description="Create new session",
     *     section="Authentication",
     *     formType="POST",
     *     filters={
     *          {"name"="csrfToken","dataType"="string"},
     *          {"name"="username","dataType"="string"},
     *          {"name"="password","dataType"="string"},
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
            return JSendResponse::createFail('Kasutajat ei leitud', 400);
        }

        // Get the encoder to check user password
        /** @var EncoderFactoryInterface $encoderFactory */
        $encoderFactory = $this->get('security.encoder_factory');
        $encoder = $encoderFactory->getEncoder($user);

        // Note the difference
        if (!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            // Get profile list
            return JSendResponse::createFail('Vale parool', 400);
        }

        // Login the user

        $token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
        $this->get("security.context")->setToken($token); // User is logged in

        // Dispatch the login event to all listeners
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

        return JSendResponse::createSuccess('Autoriseerimine õnnestus');
    }

    /**
     * Invalidate the current session and destroy any authentication data
     *
     * @ApiDoc(
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
        $this->get('security.context')->setToken(null);

        // Invalidate session
        if ($request->getSession()->invalidate()) {
            return JSendResponse::createSuccess('Välja logitud');
        } else {
            return JSendResponse::createError('Välja logimine ebaõnnestus', 500);
        }
    }

    /**
     * Note: CsrfProviderInterface is deprecated. Upgrade FosUserBundle to fix this and use CsrfTokenManagerInterface
     * @return CsrfProviderInterface
     */
    protected function getCSRFProvider()
    {
        return $this->get('form.csrf_provider'); // todo: use security.csrf.token_manager since symfony 3.0
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
