<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Plate.php';
include_once __DIR__ . '/../middleware/AdminMiddleware.php';

class AdminMenuController {
    private $db;
    private $plateModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->plateModel = new Plate($this->db);
    }
    
    // Get all plates with filters
    public function getPlates() {
        error_log("AdminMenuController: getPlates() called");
        try {
            $middleware = new AdminMiddleware();
            $isAuthenticated = true; // Temporary bypass for debugging
            error_log("AdminMenuController: isAuthenticated? " . ($isAuthenticated ? "Yes" : "No"));
            
            if (!$isAuthenticated) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $category = $_GET['category'] ?? null;
            $status = $_GET['status'] ?? null;
            $search = $_GET['search'] ?? null;
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            $sortBy = $_GET['sort'] ?? 'name';
            $sortOrder = $_GET['order'] ?? 'ASC';
            
            $query = "SELECT * FROM plates WHERE 1=1";
            $params = [];
            
            // Category filter
            if ($category) {
                $query .= " AND category = :category";
                $params['category'] = $category;
            }
            
            // Search filter
            if ($search) {
                $query .= " AND (name LIKE :search OR description LIKE :search)";
                $params['search'] = "%$search%";
            }
            
            // Status filter
            if ($status) {
                if ($status === 'available') {
                    $query .= " AND is_available = 1";
                } elseif ($status === 'unavailable') {
                    $query .= " AND is_available = 0";
                } elseif ($status === 'signature') {
                    $query .= " AND is_signature = 1";
                }
            }
            
            // Sorting
            $allowedSorts = ['name', 'price', 'category', 'base_rating', 'created_at'];
            $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'name';
            $sortOrder = in_array(strtoupper($sortOrder), ['ASC', 'DESC']) ? strtoupper($sortOrder) : 'ASC';
            
            $query .= " ORDER BY $sortBy $sortOrder LIMIT :limit OFFSET :offset";
            $params['limit'] = $limit;
            $params['offset'] = $offset;
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                if ($key === 'limit' || $key === 'offset') {
                    $stmt->bindValue(":$key", (int)$value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(":$key", $value);
                }
            }
            
            $stmt->execute();
            $plates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format plates
            foreach ($plates as &$plate) {
                $plate['status'] = $plate['is_available'] ? 'available' : 'unavailable';
                $plate['rating'] = (float)$plate['base_rating'];
                $plate['prep_time'] = $plate['prep_time'] ?? 15;
                unset($plate['is_available'], $plate['base_rating']);
            }
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM plates WHERE 1=1";
            $countParams = [];
            
            if ($category) {
                $countQuery .= " AND category = :category";
                $countParams['category'] = $category;
            }
            
            if ($search) {
                $countQuery .= " AND (name LIKE :search OR description LIKE :search)";
                $countParams['search'] = "%$search%";
            }
            
            if ($status) {
                if ($status === 'available') {
                    $countQuery .= " AND is_available = 1";
                } elseif ($status === 'unavailable') {
                    $countQuery .= " AND is_available = 0";
                } elseif ($status === 'signature') {
                    $countQuery .= " AND is_signature = 1";
                }
            }
            
            $countStmt = $this->db->prepare($countQuery);
            foreach ($countParams as $key => $value) {
                $countStmt->bindValue(":$key", $value);
            }
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode([
                'success' => true,
                'data' => $plates,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
            
        } catch (Exception $e) {
            error_log("AdminMenuController getPlates error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Get plate by ID
    public function getPlate($id) {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $query = "SELECT * FROM plates WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $plate = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$plate) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Plate not found']);
                return;
            }
            
            $plate['status'] = $plate['is_available'] ? 'available' : 'unavailable';
            $plate['rating'] = (float)$plate['base_rating'];
            $plate['prep_time'] = $plate['prep_time'] ?? 15;
            unset($plate['is_available'], $plate['base_rating']);
            
            echo json_encode(['success' => true, 'data' => $plate]);
            
        } catch (Exception $e) {
            error_log("AdminMenuController getPlate error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Create new plate
    public function createPlate() {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validation
            $required = ['name', 'description', 'category', 'price'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field $field is required"]);
                    return;
                }
            }
            
            $query = "INSERT INTO plates (name, description, category, price, image_url, is_signature, is_available, prep_time, base_rating) 
                     VALUES (:name, :description, :category, :price, :image_url, :is_signature, :is_available, :prep_time, :base_rating)";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':image_url', $data['image_url']);
            $stmt->bindValue(':is_signature', $data['is_signature'] ?? false, PDO::PARAM_BOOL);
            $stmt->bindValue(':is_available', $data['is_available'] ?? true, PDO::PARAM_BOOL);
            $stmt->bindParam(':prep_time', $data['prep_time']);
            $stmt->bindParam(':base_rating', $data['rating']);
            
            if ($stmt->execute()) {
                $plateId = $this->db->lastInsertId();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Plate created successfully',
                    'plate_id' => $plateId
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create plate']);
            }
            
        } catch (Exception $e) {
            error_log("AdminMenuController createPlate error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Update plate
    public function updatePlate($id) {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Check if plate exists
            $checkQuery = "SELECT id FROM plates WHERE id = :id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Plate not found']);
                return;
            }
            
            // Build dynamic update query
            $updateFields = [];
            $params = [':id' => $id];
            
            $allowedFields = ['name', 'description', 'category', 'price', 'image_url', 'is_signature', 'is_available', 'prep_time', 'base_rating'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            
            if (empty($updateFields)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
                return;
            }
            
            $query = "UPDATE plates SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Plate updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update plate']);
            }
            
        } catch (Exception $e) {
            error_log("AdminMenuController updatePlate error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Toggle plate status
    public function togglePlateStatus($id) {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $query = "UPDATE plates SET is_available = NOT is_available WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Plate status updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update plate status']);
            }
            
        } catch (Exception $e) {
            error_log("AdminMenuController togglePlateStatus error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Delete plate
    public function deletePlate($id) {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            // Check if plate exists
            $checkQuery = "SELECT id FROM plates WHERE id = :id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Plate not found']);
                return;
            }
            
            $query = "DELETE FROM plates WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Plate deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete plate']);
            }
            
        } catch (Exception $e) {
            error_log("AdminMenuController deletePlate error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Get menu statistics
    public function getMenuStats() {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            // Total plates
            $totalQuery = "SELECT COUNT(*) as total FROM plates";
            $totalStmt = $this->db->prepare($totalQuery);
            $totalStmt->execute();
            $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Available plates
            $availableQuery = "SELECT COUNT(*) as available FROM plates WHERE is_available = 1";
            $availableStmt = $this->db->prepare($availableQuery);
            $availableStmt->execute();
            $available = $availableStmt->fetch(PDO::FETCH_ASSOC)['available'];
            
            // Signature plates
            $signatureQuery = "SELECT COUNT(*) as signature FROM plates WHERE is_signature = 1";
            $signatureStmt = $this->db->prepare($signatureQuery);
            $signatureStmt->execute();
            $signature = $signatureStmt->fetch(PDO::FETCH_ASSOC)['signature'];
            
            // Category breakdown
            $categoryQuery = "SELECT category, COUNT(*) as count FROM plates GROUP BY category";
            $categoryStmt = $this->db->prepare($categoryQuery);
            $categoryStmt->execute();
            $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'total' => (int)$total,
                    'available' => (int)$available,
                    'unavailable' => (int)($total - $available),
                    'signature' => (int)$signature,
                    'categories' => $categories
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("AdminMenuController getMenuStats error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
}
?>
