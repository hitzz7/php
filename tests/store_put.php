<?php
use PHPUnit\Framework\TestCase;

class store_put extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        // Establish a database connection for testing
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Populate the database with sample data
        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        // Create tables and insert sample data
        // This would depend on the structure of your actual database
        // Inserting some sample data for testing the PUT requests
        $this->pdo->exec("
            CREATE TABLE products (
                id INTEGER PRIMARY KEY,
                name TEXT,
                description TEXT
            );

            CREATE TABLE items (
                id INTEGER PRIMARY KEY,
                product_id INTEGER,
                size TEXT,
                color TEXT,
                status TEXT,
                sku TEXT,
                price DECIMAL(10, 2),
                FOREIGN KEY (product_id) REFERENCES products(id)
            );

            INSERT INTO products (id, name, description) VALUES
            (1, 'Product 1', 'Description 1'),
            (2, 'Product 2', 'Description 2');

            INSERT INTO items (id, product_id, size, color, status, sku, price) VALUES
            (1, 1, 'M', 'Blue', 'active', 'ABC123', 19.99),
            (2, 1, 'L', 'Red', 'inactive', 'XYZ789', 29.99),
            (3, 2, 'S', 'Green', 'active', 'DEF456', 14.99);
        ");
    }

    protected function tearDown(): void
    {
        // Clean up database and other resources
        $this->pdo = null;
    }

    public function testUpdateProduct()
    {
        // Simulate a PUT request to update a product
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['REQUEST_URI'] = '/product/1';

        // Simulate JSON data in the request body
        $putData = [
            'name' => 'Updated Product 1',
            'description' => 'Updated Description 1',
            'items' => [
                ['id' => 1, 'size' => 'XL', 'color' => 'Green', 'status' => 'active', 'sku' => '123XYZ', 'price' => '39.99'],
                ['size' => 'S', 'color' => 'Yellow', 'status' => 'active', 'sku' => '456ABC', 'price' => '19.99'],
            ],
        ];

        // Set the request body
        file_put_contents('php://input', json_encode($putData));

        ob_start();
        include 'store.php';
        $output = ob_get_clean();

        $expected = '{"message":"Product updated successfully"}';
        $this->assertEquals($expected, $output);

        // Verify that the product is updated in the database
        $updatedProductStmt = $this->pdo->prepare("SELECT * FROM products WHERE id = 1");
        $updatedProductStmt->execute();
        $updatedProduct = $updatedProductStmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Updated Product 1', $updatedProduct['name']);
        $this->assertEquals('Updated Description 1', $updatedProduct['description']);

        // Verify that the items are updated in the database
        $updatedItemsStmt = $this->pdo->prepare("SELECT * FROM items WHERE product_id = 1");
        $updatedItemsStmt->execute();
        $updatedItems = $updatedItemsStmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(2, $updatedItems);

        // Check the details of the first updated item
        $this->assertEquals('XL', $updatedItems[0]['size']);
        $this->assertEquals('Green', $updatedItems[0]['color']);
        $this->assertEquals('active', $updatedItems[0]['status']);
        $this->assertEquals('123XYZ', $updatedItems[0]['sku']);
        $this->assertEquals('39.99', $updatedItems[0]['price']);

        // Check the details of the second updated item
        $this->assertEquals('S', $updatedItems[1]['size']);
        $this->assertEquals('Yellow', $updatedItems[1]['color']);
        $this->assertEquals('active', $updatedItems[1]['status']);
        $this->assertEquals('456ABC', $updatedItems[1]['sku']);
        $this->assertEquals('19.99', $updatedItems[1]['price']);
    }

    // Add more test cases as needed

}
?>
