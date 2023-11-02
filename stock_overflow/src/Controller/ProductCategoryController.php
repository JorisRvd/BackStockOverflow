<?php

namespace App\Controller;

use Exception;
use OpenApi\Annotations as OA;
use App\Entity\ProductCategory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

#[Route('/product/category')]
class ProductCategoryController extends AbstractController
{

    #[Route('/', name: 'app_product_category_index', methods: ['GET'])]
    /**
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des categories",
     *     @OA\JsonContent(
     *        type="string",
     *        example={{"id":1, "name": "Xbox Series"},{"id":2, "name":"Playstation 5"}}
     *     )     
     * )
     *  @OA\Tag(name="Products_category")
     * )
     *
     * @param ProductCategoryRepository $productCategoryRepository
     * @return Response
     */
    public function index(ProductCategoryRepository $productCategoryRepository): Response
    {
        return $this->json($productCategoryRepository->findAll(), 200, [], [
            "groups" => "get_category"
        ]);
    }

    #[Route('/new', name: 'app_product_category_new', methods: ['POST'])]
    /**
     * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             example={"name":"Game Boy Color"}
     *           )
     *         )
     *  )
     * @OA\Response(
     *     response=201,
     *     description="Créé et retourne la catégorie",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"id":3, "name": "Game Boy Color"}
     *     )     
     * )       
     * @OA\Tag(name="Products_category")

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
            // Désérialiser (convertir) le JSON en entité Product
            $newProductCategory = $serializer->deserialize($jsonContent, ProductCategory::class, 'json');
            
        } catch (NotEncodableValueException $e) {
            // Si le JSON fourni est "malformé" ou manquant, on prévient le client
            throw new Exception($e->getMessage(), $e->getCode());
        }
        $errors = $validator->validate($newProductCategory);

        // Y'a-t-il des erreurs ?
        if (count($errors) > 0) {
            // @todo Retourner des erreurs de validation propres
            return $this->json(throw new Exception((string)$errors ,422));
        }

        // On sauvegarde l'entité
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newProductCategory);
        $entityManager->flush();

        return $this->json($newProductCategory, 201, [], []);
    }


    #[Route('/{id}', name: 'product_category_show', methods: ['GET'])]
   /**
     * @OA\Response(
     *     response=200,
     *     description="Renvoi une catégorie",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"id":3, "name":"Nintendo Switch"}
     *     )     
     * )
     *  @OA\Tag(name="Products_category")
     * )
     * @param ProductCategoryRepository $productCategoryRepository
     * @param integer $id
     * @return Response
     */
    public function getProductCategory(ProductCategoryRepository $productCategoryRepository, int $id): Response
    {
        $productCategory = $productCategoryRepository->find($id);
        if (!$productCategory) {
           throw $this->createNotFoundException('La catégorie de produit avec l\'ID ' . $id . ' n\'existe pas.');
        }
        return $this->json($productCategory, 200, [], 
        [
            'groups' => 'get_category'
        ]);
    }

    #[Route('/edit/{id}', name: 'product_category_edit', methods: ['PATCH', 'PUT'])]
    /**
     * @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             example={"name":"PC"}
     *           )
     *         )
     *  )
     * @OA\Response(
     *     response=200,
     *     description="Modifie une catégorie",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"success_message": "Catégorie mise à jour."}
     *     )     
     * )       
     * @OA\Tag(name="Products_category")
     *
     * @param Request $request
     * @param ProductCategoryRepository $productCategoryRepository
     * @param EntityManagerInterface $entityManager
     * @param integer $id
     * @param ManagerRegistry $doctrine
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function edit(Request $request, ProductCategoryRepository $productCategoryRepository, EntityManagerInterface $entityManager, int $id, ManagerRegistry $doctrine, SerializerInterface $serializer): Response
    {

        $productCategory = $productCategoryRepository->find($id);

        if (!$productCategory) {
            throw $this->createNotFoundException('La catégorie avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $content = $request->getContent(); // Get json from request
        $updateProduct = $serializer->deserialize($content, ProductCategory::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $productCategory]);

        $entityManager->flush();

        return new JsonResponse([
            'success_message' => 'Catégorie de produit mise à jour.'
        ]);
    }

    #[Route('/{id}', name: 'product_category_delete', methods: ['DELETE'])]
    /**
     *  @OA\Response(
     *     response=200,
     *     description="Supprime une catégorie",
     *     @OA\JsonContent(
     *        type="string",
     *        example={"success_message":"Produit supprimé"}
     *     )     
     * )
     *  @OA\Tag(name="Products_category")     *
     * @param ManagerRegistry $doctrine
     * @param integer $id
     * @return Response
     */
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $productCategory = $entityManager->getRepository(ProductCategory::class)->find($id);

        if (!$productCategory) {
            throw $this->createNotFoundException(
                'La catégorie avec l\'ID ' . $id . ' n\'existe pas.'
            );
        }

        $entityManager->remove($productCategory);
        $entityManager->flush();
        return new JsonResponse([
            'success_message' => 'Catégorie de produit supprimé.'
        ]);
    }
}
