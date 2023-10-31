<?php

namespace App\Controller;

use DateTime;
use App\Entity\Order;
use App\Enums\OrderStatus;
use OpenApi\Annotations as OA;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
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

#[Route('/order')]
class OrderController extends AbstractController
{
    
    #[Route('/', name: 'order_index', methods: ['GET'])]
    /**
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des commandes",
     *     @OA\JsonContent(
     *        type="string",
     *        example={{"id": 1, "date": "2023-10-12T00:00:00+00:00", "quantity": 150, "status": "En_attente", "product": {"name": "Resident Evil Village", "price": 79},"user": { "id": 1, "first_name": "Gaël", "last_name": "Coupé"}},
     *                  {"id": 2, "date": "2023-10-13T00:00:00+00:00", "quantity": 150, "status": "Validée", "product": { "name": "World of Warcraft", "price": 35}, "user": { "id": 2, "first_name": "Alexandre", "last_name": "Rousseau"}}}
     *     )     
     * )
     *  @OA\Tag(name="Orders")
     * )
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->json($orderRepository->findAll(), 200, [], [
            "groups" => "get_orders"
        ]);
    }

    #[Route('/new', name: 'order_new', methods: ['POST'])]
    /**
     * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             example={"date":"2023-10-31", "quantity": 100, "product": 1, "user_id": 1, "status": "En_attente"}
     *           )
     *         )
     *  )
     * @OA\Response(
     *     response=201,
     *     description="Créé et retourne une commande",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"message": "Commande créée", "data": {"id": 1, "date": "2023-10-12T00:00:00+00:00", "quantity": 100, "status": "En_attente", "product": {"name": "Resident Evil Village", "price": 79},"user": { "id": 1, "first_name": "Gaël", "last_name": "Coupé"}}},          
     *     )
     * )       
     * @OA\Tag(name="Orders")
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param ManagerRegistry $doctrine
     * @param ValidatorInterface $validator
     * @param UserRepository $userRepository
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ManagerRegistry $doctrine, ValidatorInterface $validator, UserRepository $userRepository, ProductRepository $productRepository): Response
    {
        $json = $request->getContent();
        $jsonBis = json_decode($json, true);

        $date = new DateTime('now');
       
        try {
            $newOrder = $serializer->deserialize($json, Order::class, 'json');
        } catch (NotEncodableValueException $e) {
            return $this->json(
                ['error' => 'JSON invalide'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // On vérifie que la date est inférieure ou égale à la date du jour, sinon on renvoi une erreur
        if ($date <= $newOrder->getDate()) {
            return $this->json([
                "message" => "Merci de renseigner une date inférieure ou égale à la date du jour"
            ], 400);
        }

        $errors = $validator->validate($newOrder);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        // Si les champs user_id et/ou product_id sont vides, on renvoi une erreur 
        if (empty($jsonBis['user_id']) || empty($jsonBis['product_id'])) {

            return new JsonResponse(["message" => "Merci de bien remplir les champs user_id et product_id"]);

        } else {

            $user = $userRepository->find($jsonBis['user_id']);
            $product = $productRepository->find($jsonBis['product_id']);
            $newOrder->setUser($user);
            $newOrder->setProduct($product);
        }
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newOrder);
        $entityManager->flush();


        return $this->json(array_merge(
            [
                "message" => "Commande créée",
                "data" => $newOrder
            ]), 201, [], 
        [
            'groups' => 'get_orders'
        ]);
    
    }

    #[Route('/{id}', name: 'order_show', methods: ['GET'])]
    /**
     * @OA\Response(
     *     response=200,
     *     description="Retourne une commande",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"id": 1, "date": "2023-10-12T00:00:00+00:00", "quantity": 150, "status": "En_attente", "product": {"name": "Resident Evil Village", "price": 79},"user": { "id": 1, "first_name": "Gaël", "last_name": "Coupé"}}
     *     )     
     * )
     *  @OA\Tag(name="Orders")
     * )
     *
     * @param Order $order
     * @param integer $id
     * @return Response
     */
    public function show(Order $order, int $id): Response
    {
        if(!$order) {
            return new JsonResponse([
                'error_message' => "La commande avec l'id".$id."n'existe pas."
            ], Response::HTTP_NOT_FOUND);
        }
        return $this->json($order, 200, [], 
        [
            'groups' => 'get_orders'
        ]);
    }

    #[Route('/edit/{id}', name: 'order_edit', methods: ['PUT', 'PATCH'])]
    /**
     * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             example={"date":"2023-10-31", "quantity": 100, "product": 1, "user_id": 1, "status": "Validée"}
     *           )
     *         )
     *  )
     * @OA\Response(
     *     response=200,
     *     description="Met à jour une commande",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"message": "Commande mise à jour", "data": {"id": 1, "date": "2023-10-12T00:00:00+00:00", "quantity": 100, "status": "Validée", "product": {"name": "Resident Evil Village", "price": 79},"user": { "id": 1, "first_name": "Gaël", "last_name": "Coupé"}}},          
     *     )
     * )       
     * @OA\Tag(name="Orders")
     *
     * @param Request $request
     * @param integer $id
     * @param EntityManagerInterface $entityManager
     * @param ManagerRegistry $doctrine
     * @param SerializerInterface $serializer
     * @param ProductRepository $productRepository
     * @param UserRepository $userRepository
     * @return Response
     */
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, SerializerInterface $serializer,ProductRepository $productRepository, UserRepository $userRepository): Response
    {
        $entityManager = $doctrine->getManager();
        $order = $entityManager->getRepository(Order::class)->find($id);

        // On vérifie que la commande existe
        if (!$order) {
            throw $this->createNotFoundException('Le produit avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $json = $request->getContent();
        $jsonBis = json_decode($json, true);
        $updateOrder = $serializer->deserialize($json, Order::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $order]);

        $date = new DateTime('now');

        // On vérifie que la date est inférieure ou égale à la date du jour, sinon on renvoi une erreur
        if (!empty($jsonBis['date']) && $date <= $updateOrder->getDate()) {
            return $this->json([
                "message" => "Merci de renseigner une date inférieure ou égale à la date du jour"
            ], 400);
        }

        // On vérifie si on modifie le produit
        if (!empty($jsonBis['product'])) {
            $product = $productRepository->find($jsonBis['product_id']);
            $updateOrder->setProduct($product);
        }

        // On vérifie si on modifie le user qui a fait la commande
        if (!empty($jsonBis['user_id'])) {
            $user = $userRepository->find($jsonBis['user_id']);
            $updateOrder->setUser($user);
        }

        $entityManager->flush();

        return $this->json(array_merge(
            [
                "message" => "La commande à été mise à jour",
                "data" => $updateOrder
            ]),
            200, [],
            [
                'groups' => 'get_orders'
            ]
        );

    }

    #[Route('/{id}', name: 'order_delete', methods: ['DELETE'])]
    /**
     * @OA\Response(
     *     response=200,
     *     description="Supprime une commande",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"message": "Commande supprimée."}
     *     )     
     * )
     *  @OA\Tag(name="Orders")
     * )
     *
     * @param ManagerRegistry $doctrine
     * @param integer $id
     * @return Response
     */
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $order = $entityManager->getRepository(Order::class)->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Le produit avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $entityManager->remove($order);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Commande supprimée.'
        ]);
    }

    // #[Route('/orders/all', name: 'orders_all', methods: ['GET'])]
    // public function getAllProducts(OrderRepository $orderRepository): Response
    // {
    //     $orders = $orderRepository->findAll();
    
    //     return $this->json($orders, 200, [], [
    //         'groups' => 'get_orders'
    //     ]);
    // }
    
}
