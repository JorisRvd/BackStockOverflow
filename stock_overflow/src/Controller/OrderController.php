<?php

namespace App\Controller;

use App\Entity\Order;
use App\Enums\OrderStatus;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use DateTime;
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

#[Route('/order')]
class OrderController extends AbstractController
{
    
    #[Route('/', name: 'order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->json($orderRepository->findAll(), 200, [], [
            "groups" => "get_orders"
        ]);
    }

    #[Route('/new', name: 'order_new', methods: ['POST'])]
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

    #[Route('/orders/all', name: 'orders_all', methods: ['GET'])]
    public function getAllProducts(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAll();
    
        return $this->json($orders, 200, [], [
            'groups' => 'get_orders'
        ]);
    }
}
