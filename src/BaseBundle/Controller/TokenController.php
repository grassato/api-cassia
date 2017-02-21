<?php

namespace BaseBundle\Controller;

use BaseBundle\Api\ApiProblemException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class TokenController extends Controller
{
    /**
     * @Route("/token/login.{_format}",
     *     defaults={
     *     "_format": "json"
     *      },
     *     requirements={
     *     "_format": "json|xml"
     *     },
     *     name="api_get_token"
     *     )
     * @Method("POST")
     * @throws ApiProblemException
     *
     * @return array
     * @ApiDoc(
     *  section="Api Token",
     *  description="Generate new use token.",
     *  parameters={
     *  },
     *  parameters={
     *      {"name"="username", "dataType"="string", "required"=true, "description"="User username"},
     *      {"name"="password", "dataType"="string", "required"=true, "description"="User password"},
     *  },
     * statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to say hello",
     *         404={
     *           "Returned when the user is not found",
     *           "Returned when something else is not found"
     *         },
     *         401="Returned when the user is not authenticated",
     *         500="Returned when some internal server error"
     *   }
     * )
     */
    public function newTokenAction(Request $request)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $password);

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $this->get('lexik_jwt_authentication.encoder')
            ->encode([
                'username' => $user->getUsername(),
                'exp' => time() + 3600 // 1 hour expiration
            ]);
        $authenticationSuccessHandler = $this->container->get('lexik_jwt_authentication.handler.authentication_success');

        return $authenticationSuccessHandler->handleAuthenticationSuccess($user, $token);

        return new JsonResponse(['token' => $token]);
    }
}
