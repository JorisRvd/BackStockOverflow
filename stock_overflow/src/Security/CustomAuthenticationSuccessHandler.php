<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent as EventAuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Event\AuthenticationSuccessEvent;

class CustomAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser(); // Obtenez l'utilisateur à partir du token

        if (!$user instanceof UserInterface) {
            // Gérez le cas où l'utilisateur n'est pas du bon type
            // Vous pouvez lancer une exception ou gérer cette situation de manière appropriée.
        }

        // Générez le jeton JWT avec des réclamations personnalisées
        $payload = [
            'user_id' => $user->getId(),
        ];

        // Créez le jeton JWT avec les réclamations personnalisées
        $jwt = $this->jwtManager->create($user, $payload);
        // Envoyez le jeton JWT dans la réponse
        return new JsonResponse(['token' => $jwt, 'userid' => $user->getId(), 'firstName' => $user->getFirstName(), "lastName" => $user->getLastName(), 'email' => $user->getEmail()]);
}
}