<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        if (empty($_POST)) {
            return new JsonResponse([
                'error_message' => 'Une erreur est survenue'
            ], Response::HTTP_NOT_ACCEPTABLE);
        }else {
            $user->setEmail($_POST['email']);
            $user->setFirstName($_POST['firstname']);
            $user->setLastName($_POST['lastname']);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $_POST['password']
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            $role = $user->getRoles();
            $role = $role[0];

            if ($role === 'ROLE_ADMIN') {
                $is_admin = true;
            } else {
                $is_admin = false;
            }

            return new JsonResponse([
                "firstname" => $user->getFirstName(),
                "is_admin" => $is_admin
            ], 201);
        }
    }
}
