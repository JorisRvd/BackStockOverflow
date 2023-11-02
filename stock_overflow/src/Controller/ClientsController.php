<?php

namespace App\Controller;

use Exception;
use App\Entity\Clients;
use App\Form\ClientsType;
use OpenApi\Annotations as OA;
use App\Repository\ClientsRepository;
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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

#[Route('/clients')]
class ClientsController extends AbstractController
{
    #[Route('/', name: 'clients_index', methods: ['GET'])]
    /**
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des clients",
     *     @OA\JsonContent(
     *        type="string",
     *        example={{"id":"1", "company": "MaCompta.fr", "email": "paie@macompta.fr", "address": "4 rue Louis Tardy", "zip_code": 17140, "city": "Lagord", "phone":"0546451280"},
     *                  {"id": 2, "company": "Micromania", "email": "accueil@micromania.fr", "address": "4 rue de Mario", "zip_code": 17000, "city": "La Rochelle", "phone": "0546410000"}}
     *     )     
     * )
     *  @OA\Tag(name="Clients")
     *
     * @param ClientsRepository $clientsRepository
     * @return Response
     */
    public function index(ClientsRepository $clientsRepository): Response
    {
        return $this->json($clientsRepository->findAll(), 200, [], [
            'groups' => 'get_client'
        ]);
    }

    #[Route('/new', name: 'clients_new', methods: ['POST'])]
    /**
     * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             example={"company": "MaCompta.fr", "email": "paie@macompta.fr", "address": "4 rue Louis Tardy", "zip_code": 17140, "city": "Lagord", "phone":"0546451280"}
     *           )
     *         )
     *  )
     * @OA\Response(
     *     response=201,
     *     description="Créé et retourne un client",
     *     @OA\JsonContent(
     *        type="string",
     *             example={"message": "Client créé", "data": {"id":"1", "company": "MaCompta.fr", "email": "paie@macompta.fr", "address": "4 rue Louis Tardy", "zip_code": 17140, "city": "Lagord", "phone":"0546451280"}}
     *     )
     * )           
     * @OA\Tag(name="Clients")          
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param ManagerRegistry $doctrine
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {

        $jsonContent = $request->getContent();
        
        try {
            $newClient = $serializer->deserialize($jsonContent, Clients::class, 'json');
        } catch (NotEncodableValueException $e) {
             // Si le JSON fourni est "malformé" ou manquant, on prévient le client
             throw new Exception($e->getMessage(), $e->getCode());

        }
        $errors = $validator->validate($newClient);

        // Y'a-t-il des erreurs ?
        if (count($errors) > 0) {
            // @todo Retourner des erreurs de validation propres
            throw new Exception((string)$errors ,422);
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
                "groups" => "get_clients"
            ]);

    }

    #[Route('/{id}', name: 'clients_show', methods: ['GET'])]
    /**
     * @OA\Response(
     *     response=200,
     *     description="Retourne un client",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"id": 2, "company": "Micromania", "email": "accueil@micromania.fr", "address": "4 rue de Mario", "zip_code": 17000, "city": "La Rochelle", "phone": "0546410000"}
     *     )     
     * )
     *  @OA\Tag(name="Clients")
     *
     * @param ClientsRepository $clientsRepository
     * @param integer $id
     * @return Response
     */
    public function show(ClientsRepository $clientsRepository, int $id): Response
    {
        $client = $clientsRepository->find($id);
        if(!$client) {
            throw $this->createNotFoundException("Le client avec l'ID ".$id." n'existe pas.");
        }
        return $this->json($client, 200);
    }

    #[Route('/edit/{id}', name: 'clients_edit', methods: ['PUT', 'PATCH'])]
    /**
     * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             example={"company": "MaCompta.fr", "email": "paie@macompta.fr", "address": "4 rue Louis Tardy", "zip_code": 17140, "city": "Lagord", "phone":"0546451280"}
     *           )
     *         )
     *  )
     * @OA\Response(
     *     response=200,
     *     description="Modifie un client",
     *     @OA\JsonContent(
     *        type="string",
     *             example={"message": "Le client à été mis à jour", "data": {"id":"1", "company": "MaCompta.fr", "email": "paie@macompta.fr", "address": "4 rue Louis Tardy", "zip_code": 17140, "city": "Lagord", "phone":"0546451280"}}
     *     )
     * )           
     * @OA\Tag(name="Clients") 
     *
     * @param Request $request
     * @param integer $id
     * @param EntityManagerInterface $entityManager
     * @param ManagerRegistry $doctrine
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {
        $entityManager = $doctrine->getManager();
        $client = $entityManager->getRepository(Clients::class)->find($id);

        // On vérifie que la commande existe
        if (!$client) {
            throw $this->createNotFoundException('Le client avec l\'ID ' . $id . ' n\'existe pas.');
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
    /**
     * @OA\Response(
     *     response=200,
     *     description="Supprime un client",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"message": "Client supprimé."}
     *     )     
     * )
     *  @OA\Tag(name="Clients")
     *
     * @param ManagerRegistry $doctrine
     * @param integer $id
     * @return Response
     */
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $client = $entityManager->getRepository(Clients::class)->find($id);

        if (!$client) {
            throw $this->createNotFoundException('Le client avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $entityManager->remove($client);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Client supprimé.'
        ]);
    }
    
}
