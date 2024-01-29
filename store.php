<?php
//hahahahha
$host = '127.0.0.1';
$dbname = 'php1';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];

$parts = explode('/', trim($request, '/'));
$endpoint = $parts[1];
$id = isset($parts[2]) ? $parts[2] : null;



switch ($method) {
    case 'GET':
        if ($endpoint === 'product' && is_numeric($id)) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($product) {
                $product['items'] = getItemsForProduct($pdo, $product['id']);
                $product['images'] = getImagesForProduct($pdo, $product['id']);
                echo json_encode($product);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
            }
        } elseif ($endpoint === 'product') {
            $stmt = $pdo->query("SELECT * FROM products");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            foreach ($products as &$product) {
                $product['items'] = getItemsForProduct($pdo, $product['id']);
                $product['images'] = getImagesForProduct($pdo, $product['id']);
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

            if (empty($data['name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Product name is required']);
                exit();
            }
            if (empty($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'description name is required']);
                exit();
            }

            $stmt = $pdo->prepare("INSERT INTO products (name, description) VALUES (:name, :description)");
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->execute();

            $newProductId = $pdo->lastInsertId();

            foreach ($data['items'] as $item) {
                if (empty($item['size']) || empty($item['color']) || empty($item['status']) || empty($item['sku']) || empty($item['price'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid item properties']);
                    exit();
                }
                $stmt = $pdo->prepare("INSERT INTO items (product_id, size, color, status, sku, price) VALUES (:product_id, :size, :color, :status, :sku, :price)");
                $stmt->bindParam(':product_id', $newProductId, PDO::PARAM_INT);
                $stmt->bindParam(':size', $item['size'], PDO::PARAM_STR);
                $stmt->bindParam(':color', $item['color'], PDO::PARAM_STR);
                $stmt->bindParam(':status', $item['status'], PDO::PARAM_STR);
                $stmt->bindParam(':sku', $item['sku'], PDO::PARAM_STR);
                $stmt->bindParam(':price', $item['price'], PDO::PARAM_STR);
                $stmt->execute();
            }
            echo json_encode(['message' => 'Product added successfully', 'id' => $newProductId]);
        } elseif ($endpoint === 'image') {
            $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;

            if (!$product_id || !is_numeric($product_id)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid Product ID']);
                break;
            }

            if (isset($_FILES['image']['tmp_name'])) {
                $imageFile = $_FILES['image']['tmp_name'];
                $imageFileName = $_FILES['image']['name'];

                $uploadFolder = __DIR__ . '/image/';

                move_uploaded_file($imageFile, $uploadFolder . $imageFileName);

                $stmt = $pdo->prepare("INSERT INTO images (product_id, image) VALUES (:product_id, :image)");
                $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt->bindParam(':image', $imageFileName, PDO::PARAM_STR);
                $stmt->execute();

                echo json_encode(['message' => 'Image added successfully', 'product_id' => $product_id]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Image not provided']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found']);
        }
        break;

    case 'PUT':
        if ($endpoint === 'product' && is_numeric($id)) {
            $data = json_decode(file_get_contents('php://input'), true);

            $existingProductStmt = $pdo->prepare("SELECT id FROM products WHERE id = :id");
            $existingProductStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $existingProductStmt->execute();
            $existingProduct = $existingProductStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingProduct) {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
                exit();
            }

            $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->execute();

        
            

            if (isset($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (empty($item['size']) || empty($item['color']) || empty($item['status']) || empty($item['sku']) || empty($item['price'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid item properties']);
                        exit();
                    }

                    if (isset($item['id'])) {
                        $itemId = $item['id'];
                        $stmt = $pdo->prepare("UPDATE items SET size = :size, color = :color, status = :status, sku = :sku, price = :price WHERE id = :id");
                        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO items (product_id, size, color, status, sku, price) VALUES (:product_id, :size, :color, :status, :sku, :price)");
                        $stmt->bindParam(':product_id', $id, PDO::PARAM_INT);
                    }

                    $stmt->bindParam(':size', $item['size'], PDO::PARAM_STR);
                    $stmt->bindParam(':color', $item['color'], PDO::PARAM_STR);
                    $stmt->bindParam(':status', $item['status'], PDO::PARAM_STR);
                    $stmt->bindParam(':sku', $item['sku'], PDO::PARAM_STR);
                    $stmt->bindParam(':price', $item['price'], PDO::PARAM_STR);
                    $stmt->execute();
                }

                
            }
        
        echo json_encode(['message' => 'Product updated successfully']);
        
            
        }elseif ($endpoint === 'update_image') {
            $image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : null;
        
            if (!$image_id || !is_numeric($image_id)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid Image ID','debug' => $_POST]);
                return;
            }
        
            if (isset($_FILES['image']['tmp_name'])) {
                $imageFile = $_FILES['image']['tmp_name'];
                $imageFileName = $_FILES['image']['name'];
        
                $uploadFolder = __DIR__ . '/image/';
        
                move_uploaded_file($imageFile, $uploadFolder . $imageFileName);
        
                
                $stmt = $pdo->prepare("UPDATE images SET image = :image WHERE id = :image_id");
                $stmt->bindParam(':image_id', $image_id, PDO::PARAM_INT);
                $stmt->bindParam(':image', $imageFileName, PDO::PARAM_STR);
                $stmt->execute();
        
                echo json_encode(['message' => 'Image updated successfully', 'image_id' => $image_id]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Image not provided']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found']);
        }
        
        break;
        
        

    case 'DELETE':
        if ($endpoint === 'product' && is_numeric($id)) {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['message' => 'Product deleted successfully', 'id' => $id]);
        } elseif ($endpoint === 'image' && is_numeric($id)) {
            $stmt = $pdo->prepare("DELETE FR:PARAM_INOM images WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO:T);
            $stmt->execute();

            echo json_encode(['message' => 'Image deleted successfully', 'id' => $id]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
}
 
function getItemsForProduct($pdo, $productId)
{
    $stmt = $pdo->prepare("SELECT * FROM items WHERE product_id = :product_id AND status = 'active'");
    $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getImagesForProduct($pdo, $productId)
{
    $stmt = $pdo->prepare("SELECT * FROM images WHERE product_id = :product_id ");
    $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
