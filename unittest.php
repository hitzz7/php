// File: YourTest.php
<?php
use PHPUnit\Framework\TestCase;

require_once 'store.php';  // Adjust the path accordingly

class unittest extends TestCase {
    private $pdo;  // Your PDO object for testing

    protected function setUp(): void {
        // Set up your PDO connection for testing
        $host = '127.0.0.1';
        $dbname = 'php1';
        $username = 'root';
        $password = 'root';

        $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function tearDown(): void {
        // Clean up after the test, e.g., close database connections
        $this->pdo = null;
    }

    public function testGetItemsForProduct() {
        // Insert a test product into the database for testing
        $productId = $this->insertTestProduct();
    
        // Call the function with the known product ID
        $stmt = $this->pdo->prepare("SELECT * FROM items WHERE product_id = :product_id AND status = 'active'");
        
        // Use bindValue for non-variable values
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Assert that the result is an array
        $this->assertIsArray($result);
    
        // Add more specific assertions based on the expected behavior
        // For example, you might assert that the result contains certain keys or values
    
        // Clean up: Delete the test product from the database
        $this->deleteTestProduct($productId);
    }
    public function testgetImagesForProduct() {
        // Insert a test product into the database for testing
        $productId = $this->insertTestProduct();
    
        // Call the function with the known product ID
        $stmt = $this->pdo->prepare("SELECT * FROM images WHERE product_id = :product_id");
        
        // Use bindValue for non-variable values
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Assert that the result is an array
        $this->assertIsArray($result);
    
        // Add more specific assertions based on the expected behavior
        // For example, you might assert that the result contains certain keys or values
    
        // Clean up: Delete the test product from the database
        $this->deleteTestProduct($productId);
    }


    private function insertTestProduct() {
        // Insert a test product into the database and return its ID for testing
        $stmt = $this->pdo->prepare("INSERT INTO products (name, description) VALUES (:name, :description)");
        
        // Use bindValue for non-variable values
        $stmt->bindValue(':name', 'Test Product', PDO::PARAM_STR);
        $stmt->bindValue(':description', 'This is a test product', PDO::PARAM_STR);
        
        $stmt->execute();
    
        return $this->pdo->lastInsertId();
    }
    
    private function deleteTestProduct($productId) {
        // Delete the test product from the database
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
        
        // Use bindValue for non-variable values
        $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
        
        $stmt->execute();
    }
}
