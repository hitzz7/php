<?php

use PHPUnit\Framework\TestCase;

class test77 extends TestCase
{
    private $baseUri = 'http://localhost:8000/store.php'; // Replace with your actual API URL
    
    public function testGetProduct()
    {
        $productId = 1;
        $response = $this->httpRequest('GET', "/product/$productId");

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('id', $response['data']);
        // Add more assertions based on your expected response structure
    }

    public function testGetAllProducts()
    {
        $response = $this->httpRequest('GET', '/product');

        $this->assertEquals(200, $response['status']);
        $this->assertIsArray($response['data']);
        // Add more assertions based on your expected response structure
    }

    public function testAddProduct()
    {
        $data = [
            'name' => 'Test Product',
            'description' => 'Test Product Description',
            'items' => [
                [
                    'size' => 'M',
                    'color' => 'Red',
                    'status' => 'active',
                    'sku' => 'SKU123',
                    'price' => '19.99',
                ],
            ],
        ];

        $response = $this->httpRequest('POST', '/product', $data);

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('id', $response['data']);
        // Add more assertions based on your expected response structure
    }

    public function testUpdateProduct()
    {
        $productId = 1;
        $data = [
            'name' => 'Updated Product',
            'description' => 'Updated Product Description',
        ];

        $response = $this->httpRequest('PUT', "/product/$productId", $data);

        $this->assertEquals(200, $response['status']);
        // Add more assertions based on your expected response structure
    }

    public function testDeleteProduct()
    {
        $productId = 1;
        $response = $this->httpRequest('DELETE', "/product/$productId");

        $this->assertEquals(200, $response['status']);
        // Add more assertions based on your expected response structure
    }

    private function httpRequest($method, $uri, $data = [])
    {
        $url = $this->baseUri . $uri;

        $options = [
            'http' => [
                'method' => $method,
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data),
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return [
            'status' => $http_response_header[0],
            'data' => json_decode($result, true),
        ];
    }
}
