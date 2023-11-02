<?php

namespace App\Controller;

use App\Entity\Shipping;
use App\Repository\ClientsRepository;
use App\Repository\ProductRepository;
use App\Repository\ShippingRepository;
use App\Repository\UserRepository;
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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/shipping')]
class ShippingController extends AbstractController
{

    #[Route('/', name: 'shipping_index', methods: ['GET'])]
    /**
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des expéditions",
     *     @OA\JsonContent(
     *        type="string",
     *        example={{"date": "2023-10-31T14:04:10+00:00", "clients": {"company": "Micromania", "email": "accueil@micromania.fr", "address": "4 rue de Mario", "zip_code": 17000, "phone": "0546410000"},
     *                 "user": { "email": "test@stockOverflow.fr", "first_name": "Test", "last_name": "Test"}, 
     *                 "product": {{"name": "Starfield", "description": "A wonderful way to discover space and beyond", "quantity": 114, "price": 50, "is_active": true, "product_category": { "name": "XBOX S"}},
     *                 {"name": "Pokemon Bleu", "description": "Attrappez les tous", "quantity": 5, "price": 8, "is_active": true, "product_category": {"name": "Game Boy Color"}}}},
     *                 {"date": "2023-10-31T14:04:10+00:00", "clients": {"company": "Micromania", "email": "accueil@micromania.fr", "address": "4 rue de Mario", "zip_code": 17000, "phone": "0546410000"},
     *                 "user": { "email": "test@stockOverflow.fr", "first_name": "Test", "last_name": "Test"}, 
     *                 "product": {{"name": "Super Smash Bros. Ultimate", "description": "Un jeu de combat qui rassemble les personnages emblématiques de Nintendo.", "quantity": 9, "price": 49, "is_active": true, "product_category": { "name": "Nintendo Switch"}},
     *                 {"name": "Cyberpunk 2077", "description": "Un RPG futuriste se déroulant dans un monde ouvert, plein de cybernétique et d'intrigue.", "quantity": 5, "price": 35, "is_active": true, "product_category": {"name": "PS5"}}}}}
     *     )     
     * )
     *  @OA\Tag(name="Shippings")
     * )
     *
     * @param ShippingRepository $shippingRepository
     * @return Response
     */
    public function index(ShippingRepository $shippingRepository): Response
    {
        return $this->json($shippingRepository->findAll(), 200, [], [
            "groups" => "get_shippings"
        ]);
    }


    #[Route('/new', name: '= shipping_new', methods: ['POST'])]
    /**
     * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             example={"shipping": {"clients" : 1, "user" : 1}, "shippingProducts" : {{"product_id" : 1, "quantity" : 20},{"product_id" : 15,"quantity" : 5}}}
     *           )
     *         )
     *  )
     * @OA\Response(
     *     response=201,
     *     description="Créé et retourne une expédition",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"date": "2023-10-31T14:04:10+00:00", "clients": {"company": "Micromania", "email": "accueil@micromania.fr", "address": "4 rue de Mario", "zip_code": 17000, "phone": "0546410000"},
     *                 "user": { "email": "test@stockOverflow.fr", "first_name": "Test", "last_name": "Test"}, 
     *                 "product": {{"name": "Starfield", "description": "A wonderful way to discover space and beyond", "quantity": 114, "price": 50, "is_active": true, "product_category": { "name": "XBOX S"}},
     *                 {"name": "Ghost of Tsushima", "description": "Un jeu d'action-aventure qui vous plonge dans le Japon féodal.", "quantity": 7, "price": 70, "is_active": true, "product_category": {"name": "PS5"}}}},
     *     )
     * ) 
     *  @OA\Tag(name="Shippings")

     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param UserRepository $userRepository
     * @param ClientsRepository $clientRepository
     * @param ProductRepository $productRepository
     * @return Response
     */
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
            throw new Exception($e->getMessage(), $e->getCode());
        }

        $errors = $validator->validate($newShipping);

        if (count($errors) > 0) {
            throw new Exception((string)$errors ,422);
        }

        $user = $userRepository->find($jsonDecode['shipping']['user']);
        $client = $clientRepository->find($jsonDecode['shipping']['clients']);

        $date = new \DateTime();

        if (!$user) {
            throw $this->createNotFoundException("L'utilisateur avec l'ID ".$jsonDecode['shipping']['user']." n'existe pas.");

        }elseif(!$client) {
            throw $this->createNotFoundException("Le client avec l'ID ".$jsonDecode['shipping']['clients']." n'existe pas.");
        }

        if ($user && $client) {
            $newShipping->setUser($user);
            $newShipping->setClients($client);
            $newShipping->setDate($date);

            foreach ($jsonDecode['shippingProducts'] as $itemData) {
                $product = $productRepository->find($itemData['product_id']);
                

                if (!$product) {
                    throw $this->createNotFoundException("Le produit avec l'ID ".$itemData['product_id']." n'existe pas.");
                } else {
                    $newShipping->addProduct($product);
                    $quantity = $product->getQuantity();
                    if ($jsonDecode['shipping']['nb_shipped']) {
                        $newShipping->setnbShipped($jsonDecode['shipping']['nb_shipped']);
                        $newQuantity = $quantity - $jsonDecode['shipping']['nb_shipped'];
                        if ($newQuantity < 0) {
                            throw new Exception("La quantité du produit {$product->getName()} en stock n'est pas suffisante", 500);
                        }
                        $product->setQuantity($itemData['quantity']);
                    }
                }
                $entityManager->persist($newShipping);
            }
            $entityManager->flush();

            return $this->json($newShipping, Response::HTTP_CREATED, [], ['groups' => 'get_shippings']);
        }

        // return $this->json(throw new Exception('Utilisateur ou client non trouvé'));
    }

    #[Route('/{id}', name: '= shipping_show', methods: ['GET'])]
    /**
     * @OA\Response(
     *     response=200,
     *     description="Retourne une expédition",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"date": "2023-10-31T14:04:10+00:00", "clients": {"company": "Micromania", "email": "accueil@micromania.fr", "address": "4 rue de Mario", "zip_code": 17000, "phone": "0546410000"},
     *                 "user": { "email": "test@stockOverflow.fr", "first_name": "Test", "last_name": "Test"}, 
     *                 "product": {{"name": "Starfield", "description": "A wonderful way to discover space and beyond", "quantity": 114, "price": 50, "is_active": true, "product_category": { "name": "XBOX S"}},
     *                 {"name": "Ghost of Tsushima", "description": "Un jeu d'action-aventure qui vous plonge dans le Japon féodal.", "quantity": 7, "price": 70, "is_active": true, "product_category": {"name": "PS5"}}}},
     *     )
     * ) 
     *  @OA\Tag(name="Shippings")
     *
     * @param ShippingRepository $shippingRepository
     * @param integer $id
     * @return Response
     */
    public function show(ShippingRepository $shippingRepository, int $id): Response
    {
        $shipping = $shippingRepository->find($id);
        if (!$shipping) {
            throw $this->createNotFoundException('L\'expédition avec l\'ID ' . $id . ' n\'existe pas.');
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

    #[Route('/edit/{id}', name: '= shipping_edit', methods: ['PUT', 'PATCH'])]
    /**
     * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             example={"shipping": {"clients" : 1, "user" : 1}, "shippingProducts" : {{"product_id" : 1, "quantity" : 10},{"product_id" : 15,"quantity" : 5}}}
     *           )
     *         )
     *  )
     *
     * @OA\Response(
     *     response=200,
     *     description="Supprime une commande",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"message": "Expédition mise à jour"}
     *     )     
     * )
     *  @OA\Tag(name="Shippings")
     * )
     *
     * @param Request $request
     * @param ShippingRepository $shippingRepository
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param ClientsRepository $clientsRepository
     * @param SerializerInterface $serializer
     * @param ManagerRegistry $doctrine
     * @param integer $id
     * @return Response
     */
    public function edit(Request $request, ShippingRepository $shippingRepository, EntityManagerInterface $entityManager, UserRepository $userRepository, ClientsRepository $clientsRepository, SerializerInterface $serializer, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();

        $shipping = $shippingRepository->find($id);

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
            'success_message' => 'Expédition mise à jour.'
        ]);
    }

    #[Route('/{id}', name: '= shipping_delete', methods: ['DELETE'])]
    /**
      * @OA\Response(
     *     response=200,
     *     description="Supprime une commande",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"message": "Expédition supprimée"}
     *     )     
     * )
     *  @OA\Tag(name="Shippings")
     *
     * @param ManagerRegistry $doctrine
     * @param integer $id
     * @return Response
     */
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $shipping = $entityManager->getRepository(Shipping::class)->find($id);

        if (!$shipping) {
            throw $this->createNotFoundException('L\'expédition avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $entityManager->remove($shipping);
        $entityManager->flush();
        return new JsonResponse([
            'success_message' => 'Expédition supprimée.'
        ]);
    }
}
