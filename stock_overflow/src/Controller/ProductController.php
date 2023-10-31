<?php

namespace App\Controller;

use App\Entity\Product;

use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;


#[Route('/product')]

class ProductController extends AbstractController
{

    #[Route('/', name: 'product_index', methods: ['GET'])]
    /**
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des produits",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"get_products"})),
     *        example={{"id":15, "name": "Forza Horizon 5", "description": "Un jeu de course incroyable au Mexique", "price": 69, "quantity":200, "is_active": true, "product_category": {"name": "Xbox"}},
     *          {"id":56, "name": "EA FC 24", "description": "Le dernier jeu de football", "price": 69, "quantity":67, "is_active": true, "product_category": {"name": "Playstation 5"}}}
     *     )     
     * )
     *  @OA\Tag(name="Products")
     * )
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->json($productRepository->findAll(), 200, [
            'Access-Control-Allow-Origin'=>'*',
            'Content-Type'=> 'application/json'
        ],
        [
            "groups" => "get_products"
        ]);
    }
    
    #[Route('/new-products', name: 'product_new', methods: ['POST'])]
    /**
     *
     * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"name": "Forza Horizon 5", "description": "Un jeu de course incroyable au Mexique", "price": 69, "quantity":200, "is_active": true, "product_category": 1}
     *             )
     *         )
     *     )
     * @OA\Response(
     *     response=201,
     *     description="Créé un produit et le retourne",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"get_products"})),
     *        example={"id":15, "name": "Forza Horizon 5", "description": "Un jeu de course incroyable au Mexique", "price": 69, "quantity":200, "is_active": true, "product_category": {"name": "Xbox"}}
     *     )     
     * )
     *  @OA\Tag(name="Products")
     * )
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param ManagerRegistry $doctrine
     * @param ValidatorInterface $validator
     * @param ProductCategoryRepository $productCategoryRepository
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ManagerRegistry $doctrine, ValidatorInterface $validator, ProductCategoryRepository $productCategoryRepository): Response
    {
        $jsonContent = $request->getContent();
        $jsonContentBis = json_decode($jsonContent, true); 

        try {
            // Désérialiser (convertir) le JSON en entité Product
            $newProduct = $serializer->deserialize($jsonContent, Product::class, 'json');
        } catch (NotEncodableValueException $e) {
            // Si le JSON fourni est "malformé" ou manquant, on prévient le client
            return $this->json(
                ['error' => 'JSON invalide'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $errors = $validator->validate($newProduct);

        // Y'a-t-il des erreurs ?
        if (count($errors) > 0) {
            // @todo Retourner des erreurs de validation propres
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $productCategory = $productCategoryRepository->find($jsonContentBis['product_category']); 
        $newProduct->setProductCategory($productCategory); 
        // On sauvegarde l'entité
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newProduct);
        $entityManager->flush();

        return $this->json($newProduct, 201, [], [
            'groups' => 'get_products'
        ]);
        
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Renvoi un produit",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"get_products"})),
     *        example={"id": 15,"name": "Forza Horizon 5", "description": "Un jeu de course incroyable au Mexique", "price": 69, "quantity":200, "is_active": true, "product_category": 1}
     *     )     
     * )
     *  @OA\Tag(name="Products")
     * )
     * @param ProductRepository $productRepository
     * @return Response
     */
    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function getProduct(Product $product, int $id): Response
    {
        if (!$product) {
            return new JsonResponse([
                'error_message' => 'Le produit avec l\'ID ' . $id . ' n\'existe pas.'
            ], Response::HTTP_NOT_FOUND);
        }
        return $this->json($product, 200, [], 
        [
            'groups' => 'get_products'
        ]);
    }

    /**
     * 
     * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"name": "EA FC 24", "description": "Le dernier jeu de football", "price": 69, "quantity":67, "is_active": true, "product_category": 1}
     *             )
     *         )
     *     )
     * @OA\Response(
     *     response=200,
     *     description="Modifie un produit",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"get_products"})),
     *        example={"success_message": "Produit mis à jour."}
     *     )     
     * )
     *  @OA\Tag(name="Products")
     * )
     * 
     */
    #[Route('/edit/{id}', name: 'product_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, int $id, ManagerRegistry $doctrine, SerializerInterface $serializer, ProductCategoryRepository $productCategoryRepository): Response
    {
        $entityManager = $doctrine->getManager();

        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Le produit avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $content = $request->getContent(); // Get json from request
        $jsonContentBis = json_decode($content, true); 
        $updateProduct = $serializer->deserialize($content, Product::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $product]);

        $productCategory = $productCategoryRepository->find($jsonContentBis['product_category']); 
        $updateProduct->setProductCategory($productCategory);


        $entityManager->flush();

        return new JsonResponse([
            'success_message' => 'Produit mis à jour.'
        ]);
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="Supprime un produit",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"get_products"})),
     *        example={"success_message":"Produit supprimé"}
     *     )     
     * )
     *  @OA\Tag(name="Products")
     * )
     */
    #[Route('/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $entityManager->remove($product);
        $entityManager->flush();
        return new JsonResponse([
            'success_message' => 'Produit supprimé.'
        ]);
    }
    
    // #[Route('/products/all', name: 'product_all', methods: ['GET'])]
    // public function getAllProducts(ProductRepository $productRepository): Response
    // {
    //     $products = $productRepository->findAll();
    
    //     return $this->json($products, 200, [], [
    //         'groups' => 'get_products'
    //     ]);
    // }
}
