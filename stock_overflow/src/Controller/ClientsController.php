<?php

namespace App\Controller;

use App\Entity\Clients;
use App\Form\ClientsType;
use App\Repository\ClientsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

#[Route('/clients')]
class ClientsController extends AbstractController
{
    #[Route('/', name: 'clients_index', methods: ['GET'])]
    public function index(ClientsRepository $clientsRepository): Response
    {
        return $this->json($clientsRepository->findAll());
    }

    #[Route('/new', name: 'clients_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {

        $jsonContent = $request->getContent();
        
        try {
            $newClient = $serializer->deserialize($jsonContent, Clients::class, 'json');
        } catch (NotEncodableValueException $e) {
             // Si le JSON fourni est "malformé" ou manquant, on prévient le client
             return $this->json(
                ['error' => 'JSON invalide'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $errors = $validator->validate($newClient);

        // Y'a-t-il des erreurs ?
        if (count($errors) > 0) {
            // @todo Retourner des erreurs de validation propres
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // On sauvegarde l'entité
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newClient);
        $entityManager->flush();

        return $this->json(array_merge(
            [
                "message" => "Client créé",
                "data" => $newClient
            ]), 201, [], [
                ""
            ]);

    }

    #[Route('/{id}', name: 'clients_show', methods: ['GET'])]
    public function show(Clients $client, int $id): Response
    {
        if(!$client) {
            return new JsonResponse([
                'error_message' => "La commande avec l'id".$id."n'existe pas."
            ], Response::HTTP_NOT_FOUND);
        }
        return $this->json($client, 200);
    }

    #[Route('/edit/{id}', name: 'clients_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {
        $entityManager = $doctrine->getManager();
        $client = $entityManager->getRepository(Clients::class)->find($id);

        // On vérifie que la commande existe
        if (!$client) {
            throw $this->createNotFoundException('Le produit avec l\'ID ' . $id . ' n\'existe pas.');
        }
        $json = $request->getContent();
        $updateClient = $serializer->deserialize($json, Clients::class, 'json',[AbstractNormalizer::OBJECT_TO_POPULATE => $client]);

        $entityManager->flush();

        return $this->json(array_merge(
            [
                "message" => "Le client à été mis à jour",
                "data" => $updateClient
            ]), 200
        );
    }

    #[Route('/{id}', name: 'clients_delete', methods: ['DELETE'])]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $client = $entityManager->getRepository(Clients::class)->find($id);

        if (!$client) {
            throw $this->createNotFoundException('Le produit avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $entityManager->remove($client);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Client supprimée.'
        ]);
    }
}
