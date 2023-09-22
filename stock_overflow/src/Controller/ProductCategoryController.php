<?php

namespace App\Controller;

use App\Entity\ProductCategory;
use App\Form\ProductCategoryType;
use App\Repository\ProductCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;



#[Route('/product/category')]
class ProductCategoryController extends AbstractController
{
    #[Route('/', name: 'app_product_category_index', methods: ['GET'])]
    public function index(ProductCategoryRepository $productCategoryRepository): Response
    {
        return $this->render('product_category/index.html.twig', [
            'product_categories' => $productCategoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {
        $jsonContent = $request->getContent();

        try {
            // Désérialiser (convertir) le JSON en entité Product
            $newProductCategory = $serializer->deserialize($jsonContent, ProductCategory::class, 'json');
            
        } catch (NotEncodableValueException $e) {
            // Si le JSON fourni est "malformé" ou manquant, on prévient le client
            return $this->json(
                ['error' => 'JSON invalide'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $errors = $validator->validate($newProductCategory);

        // Y'a-t-il des erreurs ?
        if (count($errors) > 0) {
            // @todo Retourner des erreurs de validation propres
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // On sauvegarde l'entité
        $entityManager = $doctrine->getManager();
        $entityManager->persist($newProductCategory);
        $entityManager->flush();

        return $this->json($newProductCategory, 201, [], []);
    }

    #[Route('/{id}', name: 'app_product_category_show', methods: ['GET'])]
    public function show(ProductCategory $productCategory): Response
    {
        return $this->render('product_category/show.html.twig', [
            'product_category' => $productCategory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProductCategory $productCategory, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductCategoryType::class, $productCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product_category/edit.html.twig', [
            'product_category' => $productCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_category_delete', methods: ['POST'])]
    public function delete(Request $request, ProductCategory $productCategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productCategory->getId(), $request->request->get('_token'))) {
            $entityManager->remove($productCategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
