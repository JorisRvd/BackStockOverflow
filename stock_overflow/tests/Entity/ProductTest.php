<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\ProductCategory;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testCreateProduct()
    {
        // Créer une catégorie de produit
        $category = new ProductCategory();
        $category->setName(1);

        // Créer un produit
        $product = new Product();
        $product->setName('League of Legends');
        $product->setDescription('Le jeu du malheur');
        $product->setQuantity(100);
        $product->setPrice(999);
        $product->setIsActive(true);
        $product->setProductCategory($category);

        // Vérifier que les valeurs sont correctement définies
        $this->assertEquals('League of Legends', $product->getName());
        $this->assertEquals('Le jeu du malheur', $product->getDescription());
        $this->assertEquals(100, $product->getQuantity());
        $this->assertEquals(999, $product->getPrice());
        $this->assertTrue($product->isIsActive());
        $this->assertSame($category, $product->getProductCategory());

        echo "Les tests de création de produit se sont terminés avec succès !\n";
    }
}
