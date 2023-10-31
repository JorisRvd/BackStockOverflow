<?php

namespace App\Controller;

use App\Entity\Shipping;
use App\Entity\ShippingItem;
use App\Form\ShippingType;
use App\Repository\ClientsRepository;
use App\Repository\ProductRepository;
use App\Repository\ShippingItemRepository;
use App\Repository\ShippingRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/shipping')]
class ShippingController extends AbstractController
{

    #[Route('/new', name: '= shipping_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        ClientsRepository $clientRepository,
        ProductRepository $productRepository
    ): Response {
        $jsonContent = $request->getContent();
        $jsonDecode = json_decode($jsonContent, true);

        try {
            // Désérialiser (convertir) le JSON en entité Shipping
            $newShipping = $serializer->deserialize($jsonContent, Shipping::class, 'json');
        } catch (NotEncodableValueException $e) {
            // Si le JSON fourni est "malformé" ou manquant, prévenez le client
            return $this->json(['error' => 'JSON invalide'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $errors = $validator->validate($newShipping);

        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $userRepository->find($jsonDecode['shipping']['user']);
        $client = $clientRepository->find($jsonDecode['shipping']['clients']);

        $date = new \DateTime();

        if ($user && $client) {
            $newShipping->setUser($user);
            $newShipping->setClients($client);
            $newShipping->setDate($date);

            foreach ($jsonDecode['shippingProducts'] as $itemData) {
                $product = $productRepository->find($itemData['product_id']);

                if ($product) {
                    $newShipping->addProduct($product);
                    $quantity = $product->getQuantity();
                    if ($itemData['quantity']) {
                        $newQuantity = $quantity - $itemData['quantity'];
                        if ($newQuantity < 0) {
                            throw new Exception("La quantité du produit {$product->getName()} en stock n'est pas suffisante", 500);
                        }
                        $product->setQuantity($newQuantity);
                    }
                }
                $entityManager->persist($newShipping);
            }
            $entityManager->flush();

            return $this->json($newShipping, Response::HTTP_CREATED, [], ['groups' => 'get_shippings']);
        }

        return $this->json(['error' => 'Utilisateur ou client non trouvé']);
    }

    #[Route('/{id}', name: '= shipping_show', methods: ['GET'])]
    public function show(Shipping $shipping, int $id): Response
    {
        if (!$shipping) {
            return new JsonResponse([
                'error_message' => 'L\'expédition avec l\'ID ' . $id . ' n\'existe pas.'
            ], Response::HTTP_NOT_FOUND);
        }
        return $this->json(
            $shipping,
            200,
            [],
            [
                'groups' => 'get_shippings'
            ]
        );
    }

    #[Route('/edit/{id}', name: '= shipping_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Shipping $shipping, EntityManagerInterface $entityManager, UserRepository $userRepository, ClientsRepository $clientsRepository, SerializerInterface $serializer, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();

        $shipping = $entityManager->getRepository(Shipping::class)->find($id);

        if (!$shipping) {
            throw $this->createNotFoundException('L\'expédition avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $content = $request->getContent();
        $jsonContentBis = json_decode($content, true);
        $updateShipping = $serializer->deserialize($content, Shipping::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $shipping]);

        $user = $userRepository->find($jsonContentBis['user_id']);
        $client = $clientsRepository->find($jsonContentBis['client_id']);
        $updateShipping->setClients($client);
        $updateShipping->setUser($user);


        $entityManager->flush();

        return new JsonResponse([
            'success_message' => 'Expédition mis à jour.'
        ]);
    }

    #[Route('/{id}', name: '= shipping_delete', methods: ['POST'])]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $shipping = $entityManager->getRepository(Shipping::class)->find($id);

        if (!$shipping) {
            throw $this->createNotFoundException(
                'No shipping found for id ' . $id
            );
        }

        $entityManager->remove($shipping);
        $entityManager->flush();
        return new JsonResponse([
            'success_message' => 'Expédition supprimé.'
        ]);
    }
}
