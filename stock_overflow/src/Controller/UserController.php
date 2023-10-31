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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/user')]
class UserController extends AbstractController
{

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    /**
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des utilisateurs",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"id": 2, "email": "alex@test.fr", "roles": {"ROLE_ADMIN", "ROLE_USER"}, "first_name": "Alexandre", "last_name": "Rousseau"}
     *     )     
     * )
     *  @OA\Tag(name="Users")
     * ) 
     * @param User $user
     * @param integer $id
     * @return Response
     */
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

    #[Route('/edit/{id}', name: 'user_edit', methods: ['PUT', 'PATCH'])]
    /**
     * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             example={"email": "alex@test.fr", "roles": {"ROLE_USER"}, "first_name": "Alexandre", "last_name": "Rousseau"}
     *           )
     *         )
     *  )
     * @OA\Response(
     *     response=200,
     *     description="Modifie un utilisateur",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"id": 2, "email": "alex@test.fr", "roles": {"ROLE_USER"}, "first_name": "Alexandre", "last_name": "Rousseau"}
     *     )     
     * )
     *  @OA\Tag(name="Users")
     * ) 
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @param integer $id
     * @param ManagerRegistry $doctrine
     * @param SerializerInterface $serializer
     * @return Response
     */
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

    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    /**
     * @OA\Response(
     *     response=200,
     *     description="Supprime une commande",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"message": "User supprimé."}
     *     )     
     * )
     *
     *  @OA\Tag(name="Users")
     * @param ManagerRegistry $doctrine
     * @param integer $id
     * @return Response
     */
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
    
    #[Route('/users/all', name: 'user_all', methods: ['GET'])]
    /**
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des commandes",
     *     @OA\JsonContent(
     *        type="string",
     *        example={{"id": 1, "email": "gael@test.com", "roles": {"ROLE_USER"}, "first_name": "Gaël", "last_name": "COUPÉ"},
     *                {"id": 2, "email": "alex@test.fr", "roles": {"ROLE_ADMIN", "ROLE_USER"}, "first_name": "Alexandre", "last_name": "Rousseau"},
     *                {"id": 8, "email": "alex@alex.fr", "roles": {"ROLE_ADMIN", "ROLE_USER"}, "first_name": "Alex", "last_name": "Rousseau"}}
     *     )     
     * )
     *  @OA\Tag(name="Users")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function getAllUsers(UserRepository $userRepository): Response
    {
        $products = $userRepository->findAll();
    
        return $this->json($products, 200, [], [
            'groups' => 'get_user'
        ]);
    }
}
