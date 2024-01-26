<?php
use PHPUnit\Framework\TestCase;


class StoreTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        // Establish a database connection for testing
        $host = '127.0.0.1';
        $dbname = 'phptest';
        $username = 'root';
        $password = 'root';

        $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Populate the database with sample data
        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        // Use MySQL syntax for table creation and data insertion
        $this->pdo->exec("
            CREATE TABLE products (
                id INT PRIMARY KEY,
                name VARCHAR(255),
                description TEXT
            );

            CREATE TABLE items (
                id INT PRIMARY KEY,
                product_id INT,
                size VARCHAR(10),
                color VARCHAR(20),
                status VARCHAR(20),
                sku VARCHAR(50),
                price DECIMAL(10, 2),
                FOREIGN KEY (product_id) REFERENCES products(id)
            );

            CREATE TABLE images (
                id INT PRIMARY KEY,
                product_id INT,
                image VARCHAR(255),
                FOREIGN KEY (product_id) REFERENCES products(id)
            );

            INSERT INTO products (id, name, description) VALUES
            (1, 'Product 1', 'Description 1'),
            (2, 'Product 2', 'Description 2');

            INSERT INTO items (id, product_id, size, color, status, sku, price) VALUES
            (1, 1, 'M', 'Blue', 'active', 'ABC123', 19.99);

            INSERT INTO images (id, product_id, image) VALUES
            (1, 1, 'image1.jpg');
        ");
    }

    // protected function tearDown(): void
    // {
    //     // Clean up database and other resources
    //     $this->pdo = null;
    // }

    public function testGetSingleProduct()
    {
        // Simulate a GET request to the /product/{id} endpoint for a known product (e.g., ID 1)
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/product/1';

        // Start output buffering
        ob_start();

        // Include the script to test
        require 'C:\Test1\php\store.php';  // Replace with the actual path to your script

        // Capture the output buffer (JSON response)
        $jsonResponse = ob_get_clean();

        // Assert the HTTP response code (200 OK)
        $this->assertEquals(200, http_response_code());

        // Decode the JSON response
        $responseData = json_decode($jsonResponse, true);

        // Assert the expected structure and values based on the database setup
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(1, $responseData['id']);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertEquals('Product 1', $responseData['name']); // Adjusted assertion
        $this->assertArrayHasKey('description', $responseData);
        $this->assertEquals('Description 1', $responseData['description']); // Adjusted assertion
        $this->assertArrayHasKey('items', $responseData);
        $this->assertCount(1, $responseData['items']);

        // Adjust the assertions based on your database structure for items
        $this->assertArrayHasKey('id', $responseData['items'][0]);
        $this->assertEquals(1, $responseData['items'][0]['id']);
        $this->assertArrayHasKey('product_id', $responseData['items'][0]);
        $this->assertEquals(1, $responseData['items'][0]['product_id']);
        $this->assertArrayHasKey('size', $responseData['items'][0]);
        $this->assertEquals('M', $responseData['items'][0]['size']);
        $this->assertArrayHasKey('color', $responseData['items'][0]);
        $this->assertEquals('Blue', $responseData['items'][0]['color']);
        $this->assertArrayHasKey('status', $responseData['items'][0]);
        $this->assertEquals('active', $responseData['items'][0]['status']);
        $this->assertArrayHasKey('sku', $responseData['items'][0]);
        $this->assertEquals('ABC123', $responseData['items'][0]['sku']);
        $this->assertArrayHasKey('price', $responseData['items'][0]);
        $this->assertEquals(19.99, $responseData['items'][0]['price']);
        // Add more assertions based on your data structure for items

        // Assert the 'images' array (empty in this case)
        $this->assertArrayHasKey('images', $responseData);
        $this->assertEmpty($responseData['images']);
    }


    // public function testAddProduct()
    // {
    //     // Simulate a POST request to the /product endpoint to add a new product
    //     $_SERVER['REQUEST_METHOD'] = 'POST';
    //     $_SERVER['REQUEST_URI'] = '/product';

    //     // Provide sample JSON data for adding a new product
    //     $postData = [
    //         'name' => 'New Product',
    //         'description' => 'New Description',
    //         'items' => [
    //             [
    //                 'size' => 'M',
    //                 'color' => 'Red',
    //                 'status' => 'active',
    //                 'sku' => 'XYZ456',
    //                 'price' => 29.99,
    //             ],
    //         ],
    //     ];

    //     // Start output buffering
    //     ob_start();

    //     // Set the request body with JSON data
    //     file_put_contents('php://input', json_encode($postData));

    //     // Include the script to test
        

    //     // Capture the output buffer (JSON response)
    //     $jsonResponse = ob_get_clean();

    //     // Assert the HTTP response code (200 OK or any other expected code)
    //     $this->assertEquals(200, http_response_code());

    //     // Decode the JSON response
    //     $responseData = json_decode($jsonResponse, true);

    //     // Assert the expected structure and values based on the successful product addition
    //     $this->assertArrayHasKey('message', $responseData);
    //     $this->assertEquals('Product added successfully', $responseData['message']);
    //     $this->assertArrayHasKey('id', $responseData);
    //     $this->assertGreaterThan(0, $responseData['id']); // Assuming the ID is a positive integer
    // }

}
?>
