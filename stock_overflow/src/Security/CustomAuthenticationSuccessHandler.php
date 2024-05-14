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
        $user = $token->getUser(); // Obtention du token

        if (!$user instanceof UserInterface) {
            // Gérez le cas où l'utilisateur n'est pas du bon type
        }

        // Génération du jeton JWT avec des paramètres personnalisés
        $payload = [
            'user_id' => $user->getId(),
        ];

        // Création du jeton JWT avec les paramètres personnalisés
        $jwt = $this->jwtManager->create($user, $payload);
        // Envoi du jeton JWT dans la réponse
        return new JsonResponse(['token' => $jwt, 'userid' => $user->getId(), 'firstName' => $user->getFirstName(), "lastName" => $user->getLastName(), 'email' => $user->getEmail()]);
}
}