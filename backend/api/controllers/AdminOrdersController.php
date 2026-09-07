<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Order.php';
include_once __DIR__ . '/../middleware/AdminMiddleware.php';

class AdminOrdersController {
    private $db;
    private $orderModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->orderModel = new Order($this->db);
    }
    
    // Get all orders with filters
    public function getOrders() {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $status = $_GET['status'] ?? null;
            $period = $_GET['period'] ?? 'all';
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            
            $query = "SELECT o.*, c.name as customer_name, c.phone, c.email 
                    FROM orders o 
                    LEFT JOIN customers c ON o.customer_id = c.id";
            
            $params = [];
            $whereConditions = [];
            
            // Status filter
            if ($status) {
                $whereConditions[] = "o.status = :status";
                $params['status'] = $status;
            }
            
            // Period filter
            if ($period !== 'all') {
                $today = date('Y-m-d');
                switch ($period) {
                    case 'today':
                        $whereConditions[] = "DATE(o.created_at) = :today";
                        $params['today'] = $today;
                        break;
                    case 'week':
                        $weekStart = date('Y-m-d', strtotime('-7 days'));
                        $whereConditions[] = "DATE(o.created_at) >= :week_start";
                        $params['week_start'] = $weekStart;
                        break;
                    case 'month':
                        $monthStart = date('Y-m-01');
                        $whereConditions[] = "DATE(o.created_at) >= :month_start";
                        $params['month_start'] = $monthStart;
                        break;
                }
            }
            
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }
            
            $query .= " ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";
            $params['limit'] = $limit;
            $params['offset'] = $offset;
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format orders
            foreach ($orders as &$order) {
                $order['items'] = json_decode($order['items'], true) ?: [];
                $order['customer'] = [
                    'name' => $order['customer_name'] ?? 'Client',
                    'phone' => $order['phone'] ?? '',
                    'email' => $order['email'] ?? ''
                ];
                unset($order['customer_name'], $order['phone'], $order['email']);
            }
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM orders o";
            if (!empty($whereConditions)) {
                $countQuery .= " WHERE " . implode(' AND ', $whereConditions);
            }
            
            $countStmt = $this->db->prepare($countQuery);
            foreach ($params as $key => $value) {
                if ($key !== 'limit' && $key !== 'offset') {
                    $countStmt->bindValue(":$key", $value);
                }
            }
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode([
                'success' => true,
                'data' => $orders,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
            
        } catch (Exception $e) {
            error_log("AdminOrdersController getOrders error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Get order by ID
    public function getOrder($id) {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $query = "SELECT o.*, c.name as customer_name, c.phone, c.email, c.address 
                    FROM orders o 
                    LEFT JOIN customers c ON o.customer_id = c.id 
                    WHERE o.id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                return;
            }
            
            $order['items'] = json_decode($order['items'], true) ?: [];
            $order['customer'] = [
                'name' => $order['customer_name'] ?? 'Client',
                'phone' => $order['phone'] ?? '',
                'email' => $order['email'] ?? '',
                'address' => $order['address'] ?? ''
            ];
            
            unset($order['customer_name'], $order['phone'], $order['email'], $order['address']);
            
            echo json_encode(['success' => true, 'data' => $order]);
            
        } catch (Exception $e) {
            error_log("AdminOrdersController getOrder error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Update order status
    public function updateOrderStatus($id) {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $status = $data['status'] ?? null;
            
            if (!$status || !in_array($status, ['pending', 'preparing', 'ready', 'delivered', 'cancelled'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                return;
            }
            
            $query = "UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
            }
            
        } catch (Exception $e) {
            error_log("AdminOrdersController updateOrderStatus error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Get order statistics
    public function getOrderStats() {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $today = date('Y-m-d');
            
            // Today's orders
            $todayQuery = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue 
                          FROM orders WHERE DATE(created_at) = :today";
            $todayStmt = $this->db->prepare($todayQuery);
            $todayStmt->bindParam(':today', $today);
            $todayStmt->execute();
            $todayStats = $todayStmt->fetch(PDO::FETCH_ASSOC);
            
            // This week's orders
            $weekStart = date('Y-m-d', strtotime('-7 days'));
            $weekQuery = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue 
                        FROM orders WHERE DATE(created_at) >= :week_start";
            $weekStmt = $this->db->prepare($weekQuery);
            $weekStmt->bindParam(':week_start', $weekStart);
            $weekStmt->execute();
            $weekStats = $weekStmt->fetch(PDO::FETCH_ASSOC);
            
            // Status breakdown
            $statusQuery = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
            $statusStmt = $this->db->prepare($statusQuery);
            $statusStmt->execute();
            $statusStats = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'today' => [
                        'orders' => (int)$todayStats['count'],
                        'revenue' => (float)$todayStats['revenue']
                    ],
                    'week' => [
                        'orders' => (int)$weekStats['count'],
                        'revenue' => (float)$weekStats['revenue']
                    ],
                    'status_breakdown' => $statusStats
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("AdminOrdersController getOrderStats error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
}
?>
