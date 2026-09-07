<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Reservation.php';
include_once __DIR__ . '/../middleware/AdminMiddleware.php';

class AdminReservationsController {
    private $db;
    private $reservationModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->reservationModel = new Reservation($this->db);
    }
    
    // Get all reservations with filters
    public function getReservations() {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $status = $_GET['status'] ?? null;
            $date = $_GET['date'] ?? null;
            $guests = $_GET['guests'] ?? null;
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            $sortBy = $_GET['sort'] ?? 'date';
            $sortOrder = $_GET['order'] ?? 'ASC';
            
            $query = "SELECT * FROM reservations WHERE 1=1";
            $params = [];
            
            // Status filter
            if ($status) {
                $query .= " AND status = :status";
                $params['status'] = $status;
            }
            
            // Date filter
            if ($date) {
                switch ($date) {
                    case 'today':
                        $today = date('Y-m-d');
                        $query .= " AND DATE(reservation_date) = :today";
                        $params['today'] = $today;
                        break;
                    case 'tomorrow':
                        $tomorrow = date('Y-m-d', strtotime('+1 day'));
                        $query .= " AND DATE(reservation_date) = :tomorrow";
                        $params['tomorrow'] = $tomorrow;
                        break;
                    case 'week':
                        $weekStart = date('Y-m-d', strtotime('-7 days'));
                        $query .= " AND DATE(reservation_date) >= :week_start";
                        $params['week_start'] = $weekStart;
                        break;
                    case 'month':
                        $monthStart = date('Y-m-01');
                        $query .= " AND DATE(reservation_date) >= :month_start";
                        $params['month_start'] = $monthStart;
                        break;
                }
            }
            
            // Guests filter
            if ($guests) {
                switch ($guests) {
                    case '1-2':
                        $query .= " AND number_of_guests <= 2";
                        break;
                    case '3-4':
                        $query .= " AND number_of_guests BETWEEN 3 AND 4";
                        break;
                    case '5-8':
                        $query .= " AND number_of_guests BETWEEN 5 AND 8";
                        break;
                    case '8+':
                        $query .= " AND number_of_guests > 8";
                        break;
                }
            }
            
            // Sorting
            $allowedSorts = ['reservation_date', 'reservation_time', 'customer_name', 'number_of_guests', 'status', 'created_at'];
            $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'reservation_date';
            $sortOrder = in_array(strtoupper($sortOrder), ['ASC', 'DESC']) ? strtoupper($sortOrder) : 'ASC';
            
            $query .= " ORDER BY $sortBy $sortOrder LIMIT :limit OFFSET :offset";
            $params['limit'] = $limit;
            $params['offset'] = $offset;
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->execute();
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format reservations
            foreach ($reservations as &$reservation) {
                $reservation['date'] = $reservation['reservation_date'];
                $reservation['time'] = $reservation['reservation_time'];
                $reservation['guests'] = (int)$reservation['number_of_guests'];
                $reservation['customer_name'] = $reservation['customer_name'] ?? 'Client';
                unset($reservation['reservation_date'], $reservation['reservation_time'], $reservation['number_of_guests']);
            }
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM reservations WHERE 1=1";
            $countParams = [];
            
            if ($status) {
                $countQuery .= " AND status = :status";
                $countParams['status'] = $status;
            }
            
            if ($date) {
                switch ($date) {
                    case 'today':
                        $today = date('Y-m-d');
                        $countQuery .= " AND DATE(reservation_date) = :today";
                        $countParams['today'] = $today;
                        break;
                    case 'tomorrow':
                        $tomorrow = date('Y-m-d', strtotime('+1 day'));
                        $countQuery .= " AND DATE(reservation_date) = :tomorrow";
                        $countParams['tomorrow'] = $tomorrow;
                        break;
                    case 'week':
                        $weekStart = date('Y-m-d', strtotime('-7 days'));
                        $countQuery .= " AND DATE(reservation_date) >= :week_start";
                        $countParams['week_start'] = $weekStart;
                        break;
                    case 'month':
                        $monthStart = date('Y-m-01');
                        $countQuery .= " AND DATE(reservation_date) >= :month_start";
                        $countParams['month_start'] = $monthStart;
                        break;
                }
            }
            
            if ($guests) {
                switch ($guests) {
                    case '1-2':
                        $countQuery .= " AND number_of_guests <= 2";
                        break;
                    case '3-4':
                        $countQuery .= " AND number_of_guests BETWEEN 3 AND 4";
                        break;
                    case '5-8':
                        $countQuery .= " AND number_of_guests BETWEEN 5 AND 8";
                        break;
                    case '8+':
                        $countQuery .= " AND number_of_guests > 8";
                        break;
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
                'data' => $reservations,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
            
        } catch (Exception $e) {
            error_log("AdminReservationsController getReservations error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Get reservation by ID
    public function getReservation($id) {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $query = "SELECT * FROM reservations WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reservation) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Reservation not found']);
                return;
            }
            
            $reservation['date'] = $reservation['reservation_date'];
            $reservation['time'] = $reservation['reservation_time'];
            $reservation['guests'] = (int)$reservation['number_of_guests'];
            $reservation['customer_name'] = $reservation['customer_name'] ?? 'Client';
            unset($reservation['reservation_date'], $reservation['reservation_time'], $reservation['number_of_guests']);
            
            echo json_encode(['success' => true, 'data' => $reservation]);
            
        } catch (Exception $e) {
            error_log("AdminReservationsController getReservation error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Create new reservation
    public function createReservation() {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validation
            $required = ['customer_name', 'phone', 'email', 'reservation_date', 'reservation_time', 'number_of_guests'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field $field is required"]);
                    return;
                }
            }
            
            $query = "INSERT INTO reservations (customer_name, phone, email, reservation_date, reservation_time, number_of_guests, special_requests, status, created_at) 
                     VALUES (:customer_name, :phone, :email, :reservation_date, :reservation_time, :number_of_guests, :special_requests, :status, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':customer_name', $data['customer_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':reservation_date', $data['reservation_date']);
            $stmt->bindParam(':reservation_time', $data['reservation_time']);
            $stmt->bindParam(':number_of_guests', $data['number_of_guests']);
            $stmt->bindParam(':special_requests', $data['special_requests']);
            $stmt->bindValue(':status', $data['status'] ?? 'pending');
            
            if ($stmt->execute()) {
                $reservationId = $this->db->lastInsertId();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Reservation created successfully',
                    'reservation_id' => $reservationId
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create reservation']);
            }
            
        } catch (Exception $e) {
            error_log("AdminReservationsController createReservation error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Update reservation
    public function updateReservation($id) {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Check if reservation exists
            $checkQuery = "SELECT id FROM reservations WHERE id = :id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Reservation not found']);
                return;
            }
            
            // Build dynamic update query
            $updateFields = [];
            $params = [':id' => $id];
            
            $allowedFields = ['customer_name', 'phone', 'email', 'reservation_date', 'reservation_time', 'number_of_guests', 'special_requests', 'status'];
            
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
            
            $query = "UPDATE reservations SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Reservation updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update reservation']);
            }
            
        } catch (Exception $e) {
            error_log("AdminReservationsController updateReservation error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Update reservation status
    public function updateReservationStatus($id) {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $status = $data['status'] ?? null;
            
            if (!$status || !in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                return;
            }
            
            $query = "UPDATE reservations SET status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Reservation status updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update reservation status']);
            }
            
        } catch (Exception $e) {
            error_log("AdminReservationsController updateReservationStatus error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Delete reservation
    public function deleteReservation($id) {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            // Check if reservation exists
            $checkQuery = "SELECT id FROM reservations WHERE id = :id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Reservation not found']);
                return;
            }
            
            $query = "DELETE FROM reservations WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Reservation deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete reservation']);
            }
            
        } catch (Exception $e) {
            error_log("AdminReservationsController deleteReservation error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
    
    // Get reservation statistics
    public function getReservationStats() {
        try {
            $middleware = new AdminMiddleware();
            if (!$middleware->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $today = date('Y-m-d');
            
            // Today's reservations
            $todayQuery = "SELECT COUNT(*) as count, SUM(number_of_guests) as guests 
                         FROM reservations WHERE DATE(reservation_date) = :today";
            $todayStmt = $this->db->prepare($todayQuery);
            $todayStmt->bindParam(':today', $today);
            $todayStmt->execute();
            $todayStats = $todayStmt->fetch(PDO::FETCH_ASSOC);
            
            // This week's reservations
            $weekStart = date('Y-m-d', strtotime('-7 days'));
            $weekQuery = "SELECT COUNT(*) as count, SUM(number_of_guests) as guests 
                        FROM reservations WHERE DATE(reservation_date) >= :week_start";
            $weekStmt = $this->db->prepare($weekQuery);
            $weekStmt->bindParam(':week_start', $weekStart);
            $weekStmt->execute();
            $weekStats = $weekStmt->fetch(PDO::FETCH_ASSOC);
            
            // Status breakdown
            $statusQuery = "SELECT status, COUNT(*) as count FROM reservations GROUP BY status";
            $statusStmt = $this->db->prepare($statusQuery);
            $statusStmt->execute();
            $statusStats = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Upcoming reservations (future dates)
            $upcomingQuery = "SELECT COUNT(*) as count FROM reservations WHERE reservation_date > :today";
            $upcomingStmt = $this->db->prepare($upcomingQuery);
            $upcomingStmt->bindParam(':today', $today);
            $upcomingStmt->execute();
            $upcoming = $upcomingStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'today' => [
                        'reservations' => (int)$todayStats['count'],
                        'guests' => (int)$todayStats['guests']
                    ],
                    'week' => [
                        'reservations' => (int)$weekStats['count'],
                        'guests' => (int)$weekStats['guests']
                    ],
                    'upcoming' => (int)$upcoming,
                    'status_breakdown' => $statusStats
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("AdminReservationsController getReservationStats error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
}
?>
