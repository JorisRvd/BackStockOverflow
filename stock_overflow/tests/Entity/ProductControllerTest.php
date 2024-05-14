<?php

namespace App\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    public function testCreateProductEndpoint()
    {
        $client = static::createClient();

        $client->request('POST', '/product/new-products', [], [], [], json_encode([
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 99,
            'quantity' => 10,
            'is_active' => true,
            'product_category' => 1 
        ]));

        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Test Product', $responseData['name']);
        $this->assertEquals('Test description', $responseData['description']);
        $this->assertEquals(99, $responseData['price']);
        $this->assertEquals(10, $responseData['quantity']);
        $this->assertTrue($responseData['is_active']);
        $this->assertEquals(1, $responseData['product_category']['id']);
    }
}
