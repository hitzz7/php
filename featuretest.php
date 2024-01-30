<?php
use PHPUnit\Framework\TestCase;

class featuretest extends TestCase

{    private $pdo; // Add this property to store the PDO instance

    protected function setUp(): void
    {
        // Initialize PDO connection
        $host = '127.0.0.1';
        $dbname = 'php1';
        $username = 'root';
        $password = 'root';

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            die();
        }

        // ... other setup code ...
    }

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
        $this->assertEquals('Product added successfully', $response['data']['message']);

        // Check if the response contains the product ID
        $this->assertArrayHasKey('id', $response['data']);
    
        // Check if the product details are stored in the database
        $createdProductId = $response['data']['id'];
        $createdProduct = $this->getProductById($createdProductId);
    
        $this->assertEquals($data['name'], $createdProduct['name']);
        $this->assertEquals($data['description'], $createdProduct['description']);
    
        // Check if the item details are stored in the database
        // $createdItem = $this->getItemByProductId($createdProductId);
    
        // $this->assertEquals($data['items'][0]['size'], $createdItem['size']);
        // $this->assertEquals($data['items'][0]['color'], $createdItem['color']);
        // $this->assertEquals($data['items'][0]['status'], $createdItem['status']);
        // $this->assertEquals($data['items'][0]['sku'], $createdItem['sku']);
        // $this->assertEquals($data['items'][0]['price'], $createdItem['price']);
        
        
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
        $this->assertArrayHasKey('message', $response['data']);
        $this->assertEquals('Product updated successfully', $response['data']['message']);

        
        $updatedProduct = $this->getProductById($productId);

        $this->assertEquals($data['name'], $updatedProduct['name']);
        $this->assertEquals($data['description'], $updatedProduct['description']);

        // Check if the item details are updated in the database
        
        
        
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
    protected function getProductById($productId)
    {
        // Implement logic to fetch product details from the database
        // Adjust this based on your actual database and schema

        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    private function getItemByProductId($productId)
    {
        // Implement the logic to retrieve items by product ID from your database
        // For example:
        $stmt = $this->pdo->prepare("SELECT * FROM items WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    public function testPostProductImage()
    {
        $productId = 2; // Update with an existing product ID
        $url = $this->baseUrl . '/image';
        $imagePath = __DIR__ . '/test_image.jpg'; // Update with the path to a test image file

        $data = [
            'product_id' => $productId,
            'image' => new \CurlFile($imagePath, 'image/jpeg', 'test_image.jpg')
        ];

        // Modify the request data to include product_id as a form post variable
        $postData = [
            'product_id' => $productId,
            'image' => $data['image'],
        ];

        $response = $this->sendRequest('POST', $url, $postData);

        // Log or print the response for debugging
        var_dump($response);
        var_dump($postData);
        var_dump($data['image']);

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('message', $response['data']);
        $this->assertArrayHasKey('product_id', $response['data']);
        // You can add more assertions based on your API response structure
    }
    

    public function testUpdateProductImage()
    {
        $imageId = 1; // Update with an existing image ID
        $url = $this->baseUrl . '/update_image';
        $imagePath = __DIR__ . '/test_updated_image.jpg'; // Update with the path to a test image file

        $data = [
            'image_id' => $imageId,
            'image' => new \CurlFile($imagePath, 'image/jpeg', 'test_updated_image.jpg')
        ];

        $response = $this->sendRequest('PUT', $url, $data);

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('message', $response['data']);
        $this->assertArrayHasKey('image_id', $response['data']);
        // You can add more assertions based on your API response structure
    }

    public function testDeleteProductImage()
    {
        $imageIdToDelete = 2; // Update with an existing image ID to delete
        $url = $this->baseUrl . '/image/' . $imageIdToDelete;

        $response = $this->sendRequest('DELETE', $url);

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('message', $response['data']);
        // You can add more assertions based on your API response structure
    }

    

}
