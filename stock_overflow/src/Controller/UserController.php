<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/user')]
class UserController extends AbstractController
{

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user, int $id): Response
    {
        if (!$user) {
            return new JsonResponse([
                'error_message' => "L'utilisateur' avec l\'ID ' . $id . ' n\'existe pas."
            ], Response::HTTP_NOT_FOUND);
        }
        return $this->json($user, 200, [], 
        [
            'groups' => 'get_user'
        ]);
    }

    #[Route('/edit/{id}', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, int $id, ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {
        $entityManager = $doctrine->getManager();

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException("L'utilisateur' avec l\'ID ' . $id . ' n\'existe pas");
        }

        $content = $request->getContent();
        $updateUser = $serializer->deserialize($content, User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);

        $entityManager->flush();

        return new JsonResponse([
            'success_message' => 'User mis à jour.'
        ]);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id '.$id
            );
        }

        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse([
            'success_message' => 'User supprimé.'
        ]);
    }
}
