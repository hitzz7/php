<?php
use PHPUnit\Framework\TestCase;

class apitest extends TestCase
{
    private $baseUrl = 'http://localhost:8000/store.php';
     // Update with your actual API base URL

     public function testGetProductById()
     {
         $productId = 2; // Update with an existing product ID
     
         $url = $this->baseUrl . '/product/' . $productId;
         $response = $this->sendRequest('GET', $url);
     
         $this->assertEquals(200, $response['status']);
         $this->assertArrayHasKey('id', $response['data']);
         $this->assertEquals($productId, $response['data']['id']);
         $this->assertArrayHasKey('items', $response['data']);
         $this->assertArrayHasKey('images', $response['data']);
     
         // Check the structure of the 'items' array
         $this->assertIsArray($response['data']['items']);
         foreach ($response['data']['items'] as $item) {
             $this->assertArrayHasKey('id', $item);
             $this->assertArrayHasKey('product_id', $item);
             $this->assertArrayHasKey('size', $item);
             $this->assertArrayHasKey('color', $item);
             $this->assertArrayHasKey('status', $item);
             $this->assertArrayHasKey('sku', $item);
             $this->assertArrayHasKey('price', $item);
             // Add more specific assertions based on the structure of your 'items' array
         }
     
         // Check the structure of the 'images' array
         $this->assertIsArray($response['data']['images']);
         foreach ($response['data']['images'] as $image) {
             $this->assertArrayHasKey('id', $image);
             $this->assertArrayHasKey('product_id', $image);
             $this->assertArrayHasKey('image', $image);
             // Add more specific assertions based on the structure of your 'images' array
         }
     }

     public function testGetAllProducts()
     {
         $url = $this->baseUrl . '/product';
         $response = $this->sendRequest('GET', $url);
     
         $this->assertEquals(200, $response['status']);
         $this->assertIsArray($response['data']);
         $this->assertGreaterThan(0, count($response['data']));
     
         foreach ($response['data'] as $product) {
             $this->assertArrayHasKey('id', $product);
             $this->assertArrayHasKey('name', $product);
             $this->assertArrayHasKey('description', $product);
             $this->assertArrayHasKey('items', $product);
             $this->assertArrayHasKey('images', $product);
     
             // Check the structure of the 'items' array for each product
             $this->assertIsArray($product['items']);
             foreach ($product['items'] as $item) {
                 $this->assertArrayHasKey('id', $item);
                 $this->assertArrayHasKey('size', $item);
                 $this->assertArrayHasKey('color', $item);
                 $this->assertArrayHasKey('status', $item);
                 $this->assertArrayHasKey('sku', $item);
                 $this->assertArrayHasKey('price', $item);
                 // Add more specific assertions based on the structure of your 'items' array
             }
     
             // Check the structure of the 'images' array for each product
             $this->assertIsArray($product['images']);
             foreach ($product['images'] as $image) {
                 $this->assertArrayHasKey('id', $image);
                 $this->assertArrayHasKey('product_id', $image);
                 $this->assertArrayHasKey('image', $image);
                 // Add more specific assertions based on the structure of your 'images' array
             }
         }
     }
     

    public function testGetNonExistingProduct()
    {
        $nonExistingProductId = 999; // Update with a non-existing product ID

        $url = $this->baseUrl . '/product/' . $nonExistingProductId;
        $response = $this->sendRequest('GET', $url);

        $this->assertEquals(404, $response['status']);
        $this->assertArrayHasKey('error', $response['data']);
        $this->assertEquals('Product not found', $response['data']['error']);
    }

    
    public function testPostProduct()
    {
        $url = $this->baseUrl . '/product';
        $data = [
            'name' => 'New Product',
            'description' => 'This is a new product description',
            'items' => [
                [
                    'size' => 'Large',
                    'color' => 'Blue',
                    'status' => 'available',
                    'sku' => 'SKU123',
                    'price' => '29.99'
                ]
            ]
        ];

        $response = $this->sendRequest('POST', $url, $data);

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('message', $response['data']);
        
        
    }

    public function testUpdateProduct()
    {
        $productId = 2; // Update with an existing product ID
        $url = $this->baseUrl . '/product/' . $productId;
        $data = [
            'name' => 'Updated Product Name',
            'description' => 'Updated product description',
            'items' => [
                [
                    'id' => 1, // Update with an existing item ID for the product
                    'size' => 'Medium',
                    'color' => 'Red',
                    'status' => 'out of stock',
                    'sku' => 'SKU456',
                    'price' => '39.99'
                ]
            ]
        ];

        $response = $this->sendRequest('PUT', $url, $data);

        $this->assertEquals(200, $response['status']);
        
        // You can add more assertions based on your API response structure
    }

    public function testDeleteProduct()
    {
        $productIdToDelete = 4; // Update with an existing product ID to delete
        $url = $this->baseUrl . '/product/' . $productIdToDelete;

        $response = $this->sendRequest('DELETE', $url);

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('message', $response['data']);
        // You can add more assertions based on your API response structure
    }

    private function sendRequest($method, $url, $data = [])
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method === 'POST' || $method === 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return ['status' => $status, 'data' => json_decode($response, true)];
    }
    // public function testPostProductImage()
    // {
    //     $productId = 2; // Update with an existing product ID
    //     $url = $this->baseUrl . '/image';
    //     $imagePath = __DIR__ . '/test_image.jpg'; // Update with the path to a test image file

    //     $data = [
    //         'product_id' => $productId,
    //         'image' => new \CurlFile($imagePath, 'image/jpeg', 'test_image.jpg')
    //     ];

    //     $response = $this->sendRequest('POST', $url, $data);

    //     $this->assertEquals(200, $response['status']);
    //     $this->assertArrayHasKey('message', $response['data']);
    //     $this->assertArrayHasKey('product_id', $response['data']);
    //     // You can add more assertions based on your API response structure
    // }
    

    // public function testUpdateProductImage()
    // {
    //     $imageId = 1; // Update with an existing image ID
    //     $url = $this->baseUrl . '/update_image';
    //     $imagePath = __DIR__ . '/test_updated_image.jpg'; // Update with the path to a test image file

    //     $data = [
    //         'image_id' => $imageId,
    //         'image' => new \CurlFile($imagePath, 'image/jpeg', 'test_updated_image.jpg')
    //     ];

    //     $response = $this->sendRequest('PUT', $url, $data);

    //     $this->assertEquals(200, $response['status']);
    //     $this->assertArrayHasKey('message', $response['data']);
    //     $this->assertArrayHasKey('image_id', $response['data']);
    //     // You can add more assertions based on your API response structure
    // }

    // public function testDeleteProductImage()
    // {
    //     $imageIdToDelete = 2; // Update with an existing image ID to delete
    //     $url = $this->baseUrl . '/image/' . $imageIdToDelete;

    //     $response = $this->sendRequest('DELETE', $url);

    //     $this->assertEquals(200, $response['status']);
    //     $this->assertArrayHasKey('message', $response['data']);
    //     // You can add more assertions based on your API response structure
    // }

    

}
