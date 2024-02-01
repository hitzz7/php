<?php

class Database {
    private $host = '127.0.0.1';
    private $dbname = 'php1';
    private $username = 'root';
    private $password = 'root';
    protected $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            die();
        }
    }
}

class Product extends Database {
    public function getAllProducts() {
        $stmt = $this->pdo->query("SELECT * FROM products");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        

    }
    public function getItemsForProduct($productId) {
        $stmt = $this->pdo->prepare("SELECT * FROM items WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getImagesForProduct($productId) {
        $stmt = $this->pdo->prepare("SELECT * FROM images WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addProduct($name, $description, $items) {
        try {
            // Begin a transaction
            $this->pdo->beginTransaction();
    
            // Insert the product information
            $stmt = $this->pdo->prepare("INSERT INTO products (name, description) VALUES (:name, :description)");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->execute();
    
            // Get the new product's ID
            $newProductId = $this->pdo->lastInsertId();
    
            // Insert items for the new product
            foreach ($items as $item) {
                $this->insertItemForProduct($newProductId, $item);
            }
    
            // Commit the transaction
            $this->pdo->commit();
    
            return $newProductId;
        } catch (Exception $e) {
            // An error occurred, rollback the transaction
            $this->pdo->rollBack();
            throw $e; // Re-throw the exception for handling at a higher level
        }
    }
    
    private function insertItemForProduct($productId, $item) {
        $stmt = $this->pdo->prepare("INSERT INTO items (product_id, size, color, status, sku, price) 
                                    VALUES (:product_id, :size, :color, :status, :sku, :price)");
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':size', $item['size'], PDO::PARAM_STR);
        $stmt->bindParam(':color', $item['color'], PDO::PARAM_STR);
        $stmt->bindParam(':status', $item['status'], PDO::PARAM_STR);
        $stmt->bindParam(':sku', $item['sku'], PDO::PARAM_STR);
        $stmt->bindParam(':price', $item['price'], PDO::PARAM_STR);
        $stmt->execute();
    }
    public function updateProduct($id, $name, $description, $items) {
        // Implement logic to update a product and its items
        // ...

        return true; // Success
    }

    public function deleteProduct($id) {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return true; // Success
    }
    
    
    // Add more methods as needed
}

// Usage example:

$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];

$parts = explode('/', trim($request, '/'));
$endpoint = $parts[1];
$id = isset($parts[2]) ? $parts[2] : null;

$productAPI = new Product();

switch ($method) {
    case 'GET':
        if ($endpoint === 'product' && is_numeric($id)) {
            $product = $productAPI->getProductById($id);

            if ($product) {
                $product['items'] = $productAPI->getItemsForProduct($id);
                $product['images'] = $productAPI->getImagesForProduct($id);
                echo json_encode($product);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
            }
        } elseif ($endpoint === 'product') {
            $products = $productAPI->getAllProducts();

            foreach ($products as &$product) {
                $product['items'] = $productAPI->getItemsForProduct($product['id']);
                $product['images'] = $productAPI->getImagesForProduct($product['id']);
            }

            echo json_encode($products);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found']);
        }
        break;
        case 'POST':
            if ($endpoint === 'product') {
                $data = json_decode(file_get_contents('php://input'), true);
    
                // Validate the incoming data
                if (empty($data['name']) || empty($data['description']) || empty($data['items'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Incomplete data']);
                    exit();
                }
    
                try {
                    // Add a new product
                    $newProductId = $productAPI->addProduct($data['name'], $data['description'], $data['items']);
    
                    // Respond with the new product's ID
                    echo json_encode(['message' => 'Product added successfully', 'id' => $newProductId]);
                } catch (Exception $e) {
                    // Handle exceptions, such as database errors
                    http_response_code(500);
                    echo json_encode(['error' => 'Internal Server Error']);
                }
            } elseif ($endpoint === 'image') {
                // Handle image upload for a product
                // ...
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Not Found']);
            }
            break;

    // Handle other HTTP methods (POST, PUT, DELETE) as needed
    // ...

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
}
?>
