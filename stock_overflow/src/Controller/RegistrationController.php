<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use OpenApi\Annotations as OA;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    /**
      * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             example={"email": "test@test.fr", "first_name": "Test", "last_name": "Test"}
     *           )
     *         )
     *  )
     * @OA\Response(
     *     response=201,
     *     description="Créé un utilisateur",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"message": "Thank you for registering"}
     *     )     
     * )
     *  @OA\Tag(name="Users")
     * ) 
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, SerializerInterface $serializer, ValidatorInterface $validator, ManagerRegistry $doctrine): Response
    {
     // Récupérer le contenu JSON
     $jsonContent = $request->getContent();
     //dd($jsonContent);
     try {
         // Désérialiser (convertir) le JSON en entité Doctrine User
         $newUser = $serializer->deserialize($jsonContent, User::class, 'json');
         
     } catch (NotEncodableValueException $e) {
         // Si le JSON fourni est "malformé" ou manquant, on prévient le client
         throw new Exception($e->getMessage(), $e->getCode());

     }
     
      //hash password
      
      $hashedPassword = $userPasswordHasher->hashPassword($newUser,$newUser->getPassword());
      // On écrase le mot de passe en clair par le mot de passe haché
      $newUser->setPassword($hashedPassword);
      
      $newUser->setRoles(['ROLE_USER']); 

      // Valider l'entité
     $errors = $validator->validate($newUser);

     // Y'a-t-il des erreurs ?
     if (count($errors) > 0) {
         // @todo Retourner des erreurs de validation propres
         throw new Exception((string)$errors ,422);
        }

     
     // On sauvegarde l'entité
     $em = $doctrine->getManager();
     $em->persist($newUser);
     $em->flush();
     return new JsonResponse([
       'success_message' => 'Merci de vous être inscrit.'
     ]);
     
    }
}
