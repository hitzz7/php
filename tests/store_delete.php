<?php
use PHPUnit\Framework\TestCase;
include 'store.php';
class store_delete extends TestCase
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
        // Inserting some sample data for testing the DELETE requests
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

    public function testDeleteProduct()
    {
        // Simulate a DELETE request to delete a product
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = '/product/1';

        ob_start();
        
        $output = ob_get_clean();

        $expected = '{"message":"Product deleted successfully","id":1}';
        $this->assertEquals($expected, $output);

        // Verify that the product is deleted from the database
        $deletedProductStmt = $this->pdo->prepare("SELECT * FROM products WHERE id = 1");
        $deletedProductStmt->execute();
        $deletedProduct = $deletedProductStmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse($deletedProduct);

        // Verify that the associated items are also deleted from the database
        $deletedItemsStmt = $this->pdo->prepare("SELECT * FROM items WHERE product_id = 1");
        $deletedItemsStmt->execute();
        $deletedItems = $deletedItemsStmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(0, $deletedItems);
    }

    public function testDeleteImage()
    {
        // Simulate a DELETE request to delete an image
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = '/image/1';

        ob_start();
        
        $output = ob_get_clean();

        $expected = '{"message":"Image deleted successfully","id":1}';
        $this->assertEquals($expected, $output);

        // Verify that the image is deleted from the database
        $deletedImageStmt = $this->pdo->prepare("SELECT * FROM images WHERE id = 1");
        $deletedImageStmt->execute();
        $deletedImage = $deletedImageStmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse($deletedImage);
    }

    // Add more test cases as needed

}
?>
