<?php


class Database {
    private $host = '127.0.0.1';
    private $dbname = 'php7';
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
    // ... (existing code)

    /**
     * Executes an SQL query with optional parameters and returns the result.
     * 
     * @param string $sql The SQL query to execute.
     * @param array $params Optional parameters to bind to the query.
     * @param bool $fetchAll Determines if all rows should be returned (true) or just one (false).
     * @return mixed The result set or status of the operation.
     */
    public function executeQuery($sql, $params = [], $fetchAll = true, $returnLastInsertId = false) {
        try {
            $stmt = $this->pdo->prepare($sql);
    
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
    
            $stmt->execute();
    
            if (strpos(strtoupper($sql), 'SELECT') !== false) {
                return $fetchAll ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                if ($returnLastInsertId) {
                    return $this->pdo->lastInsertId();
                } else {
                    return $stmt->rowCount(); // For INSERT, UPDATE, DELETE
                }
            }
        } catch (PDOException $e) {
            // Handle exceptions, log errors, or re-throw for higher-level handling
            echo "Query execution failed: " . $e->getMessage();
            die();
        }
    }
    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }
    
    public function commit() {
        $this->pdo->commit();
    }
    
    public function rollBack() {
        $this->pdo->rollBack();
    }
    public function lastInsertId() {
        $this->pdo->lastInsertId();
    }
    
    
}

class Product extends Database {
    public function getAllProducts() {
        $sql = "SELECT * FROM products";
        return $this->executeQuery($sql, [], true);
    }

    public function getItemsForProduct($productId) {
        $sql = "SELECT * FROM items WHERE product_id = :product_id";
        $params = [':product_id' => $productId];
        return $this->executeQuery($sql, $params, true);
    }

    public function getImagesForProduct($productId) {
        $sql = "SELECT * FROM images WHERE product_id = :product_id";
        $params = [':product_id' => $productId];
        return $this->executeQuery($sql, $params, true);
    }

    public function getProductById($id) {
        $sql = "SELECT * FROM products WHERE id = :id";
        $params = [':id' => $id];
        return $this->executeQuery($sql, $params, false);
    }

    public function addProduct($name, $description, $items) {
        try {
            $this->beginTransaction();

            $sql = "INSERT INTO products (name, description) VALUES (:name, :description)";
            $params = [':name' => $name, ':description' => $description];
            $this->executeQuery($sql, $params, false);

            $newProductId = $this->pdo->lastInsertId();

            foreach ($items as $item) {
                $this->insertItemForProduct($newProductId, $item);
            }

            $this->commit();

            return $newProductId;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    private function insertItemForProduct($productId, $item) {
        $sql = "INSERT INTO items (product_id, size, color, status, sku, price) 
                VALUES (:product_id, :size, :color, :status, :sku, :price)";
        $params = [
            ':product_id' => $productId,
            ':size' => $item['size'],
            ':color' => $item['color'],
            ':status' => $item['status'],
            ':sku' => $item['sku'],
            ':price' => $item['price']
        ];
        $this->executeQuery($sql, $params, false);
    }

    public function updateProduct($id, $name, $description, $items) {
        try {
            $this->beginTransaction();

            $sql = "UPDATE products SET name = :name, description = :description WHERE id = :id";
            $params = [':id' => $id, ':name' => $name, ':description' => $description];
            $this->executeQuery($sql, $params, false);

            foreach ($items as $item) {
                if (isset($item['id']) && $item['id']) {
                    $this->updateItem($item);
                } else {
                    $this->insertItemForProduct($id, $item);
                }
            }

            $this->commit();

            return true;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    // ... (other methods)
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
