<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
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
         return $this->json(
             ['error' => 'JSON invalide'],
             Response::HTTP_UNPROCESSABLE_ENTITY
         );
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
         return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
     }

     
     // On sauvegarde l'entité
     $em = $doctrine->getManager();
     $em->persist($newUser);
     $em->flush();
     return new JsonResponse([
       'success_message' => 'Thank you for registering'
     ]);
     
    }
}
