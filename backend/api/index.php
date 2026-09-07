<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

// Set JSON headers first & CORS Configuration
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$frontend_url = getenv('FRONTEND_URL') ?: ($_ENV['FRONTEND_URL'] ?? '');

if (!empty($origin)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Origin, Accept");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Debug: Log all requests
error_log("=== API REQUEST ===");
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Query String: " . ($_SERVER['QUERY_STRING'] ?? 'None'));
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'None'));
error_log("Raw Input: " . file_get_contents('php://input'));
error_log("==================");

// Custom error handler to ensure JSON responses
function handleError($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error: $errno - $errstr in $errfile on line $errline");
    
    if (!headers_sent()) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(500);
    }
    
    $isDev = (getenv('APP_ENV') === 'development' || getenv('APP_DEBUG') === 'true');
    $response = [
        'success' => false,
        'message' => 'Internal server error'
    ];
    if ($isDev) {
        $response['error'] = $errstr;
        $response['file'] = $errfile;
        $response['line'] = $errline;
    }
    
    echo json_encode($response);
    exit();
}

// Custom exception handler
function handleException($exception) {
    error_log("PHP Exception: " . $exception->getMessage());
    
    if (!headers_sent()) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(500);
    }
    
    $isDev = (getenv('APP_ENV') === 'development' || getenv('APP_DEBUG') === 'true');
    $response = [
        'success' => false,
        'message' => 'Internal server error'
    ];
    if ($isDev) {
        $response['error'] = $exception->getMessage();
    }
    
    echo json_encode($response);
    exit();
}

set_error_handler('handleError');
set_exception_handler('handleException');

// Include required files with error handling
try {
    include_once __DIR__ . '/config/database.php';
    include_once __DIR__ . '/models/Plate.php';
    include_once __DIR__ . '/models/Order.php';
    include_once __DIR__ . '/models/Reservation.php';
} catch (Exception $e) {
    error_log("Include error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error'
    ]);
    exit();
}

try {
    error_log("Attempting database connection...");
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        error_log("Database connection successful!");
    } else {
        error_log("Database connection returned null!");
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Helper to check route
function isRoute($uri, $path) {
    return str_contains($uri, $path) || str_contains($_SERVER['QUERY_STRING'] ?? '', $path);
}

// Debug: Log parsed URI
error_log("Parsed URI: " . $uri);
error_log("Method: " . $method);
error_log("Query String: " . ($_SERVER['QUERY_STRING'] ?? 'none'));

// --- SERVE IMAGES ---
if ($method === 'GET' && isset($_GET['file'])) {
    $imageFile = $_GET['file'] ?? '';
    if (empty($imageFile)) {
        http_response_code(400);
        echo json_encode(["error" => "Image file parameter required"]);
        exit;
    }
    
    // Security: only allow specific file extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $fileExtension = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        http_response_code(403);
        echo json_encode(["error" => "File type not allowed"]);
        exit;
    }
    
    // Construct the full path to the image
    $imagePath = __DIR__ . '/assets/' . basename($imageFile);
    if (!file_exists($imagePath)) {
        $imagePath = __DIR__ . '/../../frontend/src/assets/' . basename($imageFile);
    }
    
    if (!file_exists($imagePath)) {
        http_response_code(404);
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(["error" => "Image not found"]);
        exit;
    }
    
    // Get MIME type
    $mimeType = mime_content_type($imagePath);
    
    // Serve image
    header("Content-Type: $mimeType");
    header("Cache-Control: public, max-age=31536000"); // Cache for 1 year
    readfile($imagePath);
}

// --- GET PLATES ---
elseif ($method === 'GET' && (isRoute($uri, 'plates') || $uri === '/' || $uri === '' || $uri === '/index.php' || $uri === '/api' || $uri === '/api/' || $uri === '/api/index.php' || $uri === '/backend/api' || $uri === '/backend/api/' || $uri === '/backend/api/index.php') && empty($_GET['action']) && empty($_GET['route']) && empty($_GET['file'])) {
    $plate = new Plate($db);
    $stmt = $plate->readAll();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $plates_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['image'] = $row['image_url']; // Map for frontend
            $plates_arr[] = $row;
        }
        echo json_encode($plates_arr);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "No plates found."]);
    }
}

// --- POST REVIEW ---
elseif ($method === 'POST' && isRoute($uri, 'review')) {
    $data = json_decode(file_get_contents("php://input"));
    $plate = new Plate($db);
    if ($plate->addReview($data->plate_id, $data->user_name ?? 'Anonyme', $data->rating, $data->comment ?? '')) {
        http_response_code(201);
        echo json_encode(["message" => "Review added."]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Error."]);
    }
}

// --- POST ORDER ---
elseif ($method === 'POST' && isRoute($uri, 'order')) {
    $data = json_decode(file_get_contents("php://input"));
    $order = new Order($db);
    $tracking = $order->create($data);
    if ($tracking) {
        http_response_code(201);
        echo json_encode(["message" => "Order created.", "tracking" => $tracking]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Error."]);
    }
}

// --- POST RESERVATION ---
elseif ($method === 'POST' && isRoute($uri, 'reservation')) {
    $data = json_decode(file_get_contents("php://input"));
    $res = new Reservation($db);
    if ($res->create($data)) {
        http_response_code(201);
        echo json_encode(["message" => "Reservation confirmed."]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Error."]);
    }
}

// --- POST ADMIN LOGIN ---
elseif ($method === 'POST' && (isRoute($uri, 'admin/login') || (isset($_GET['action']) && $_GET['action'] === 'admin/login'))) {
    try {
        include_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
    } catch (Exception $e) {
        error_log("AuthController error: " . $e->getMessage());
        $isDev = (getenv('APP_ENV') === 'development' || getenv('APP_DEBUG') === 'true');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $isDev ? 'Authentication error: ' . $e->getMessage() : 'Une erreur interne est survenue'
        ]);
    }
}

// --- POST ADMIN VERIFY OTP ---
elseif ($method === 'POST' && isRoute($uri, 'admin/verify-otp')) {
    include_once __DIR__ . '/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->verifyOTP();
}

// --- POST ADMIN LOGOUT ---
elseif ($method === 'POST' && isRoute($uri, 'admin/logout')) {
    include_once __DIR__ . '/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->logout();
}

// --- ADMIN ORDERS ROUTES ---
elseif (isRoute($uri, 'admin/orders')) {
    include_once __DIR__ . '/controllers/AdminOrdersController.php';
    $controller = new AdminOrdersController();
    
    if ($method === 'GET' && isset($_GET['stats'])) {
        $controller->getOrderStats();
    } elseif ($method === 'GET') {
        if (isset($_GET['id'])) {
            $controller->getOrder($_GET['id']);
        } else {
            $controller->getOrders();
        }
    } elseif ($method === 'PUT' && isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'status') {
        $controller->updateOrderStatus($_GET['id']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
}

// --- ADMIN MENU ROUTES ---
elseif (isRoute($uri, 'admin/menu')) {
    include_once __DIR__ . '/controllers/AdminMenuController.php';
    $controller = new AdminMenuController();
    
    if ($method === 'GET' && isset($_GET['stats'])) {
        $controller->getMenuStats();
    } elseif ($method === 'GET') {
        if (isset($_GET['id'])) {
            $controller->getPlate($_GET['id']);
        } else {
            $controller->getPlates();
        }
    } elseif ($method === 'POST') {
        $controller->createPlate();
    } elseif ($method === 'PUT' && isset($_GET['id'])) {
        if (isset($_GET['action']) && $_GET['action'] === 'toggle') {
            $controller->togglePlateStatus($_GET['id']);
        } else {
            $controller->updatePlate($_GET['id']);
        }
    } elseif ($method === 'DELETE' && isset($_GET['id'])) {
        $controller->deletePlate($_GET['id']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
}

// --- ADMIN RESERVATIONS ROUTES ---
elseif (isRoute($uri, 'admin/reservations')) {
    include_once __DIR__ . '/controllers/AdminReservationsController.php';
    $controller = new AdminReservationsController();
    
    if ($method === 'GET' && isset($_GET['stats'])) {
        $controller->getReservationStats();
    } elseif ($method === 'GET') {
        if (isset($_GET['id'])) {
            $controller->getReservation($_GET['id']);
        } else {
            $controller->getReservations();
        }
    } elseif ($method === 'POST') {
        $controller->createReservation();
    } elseif ($method === 'PUT' && isset($_GET['id'])) {
        if (isset($_GET['action']) && $_GET['action'] === 'status') {
            $controller->updateReservationStatus($_GET['id']);
        } else {
            $controller->updateReservation($_GET['id']);
        }
    } elseif ($method === 'DELETE' && isset($_GET['id'])) {
        $controller->deleteReservation($_GET['id']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
}

// --- GET LOYALTY ---
elseif ($method === 'GET' && isRoute($uri, 'loyalty')) {
    $phone = $_GET['phone'] ?? '';
    if (empty($phone)) {
        http_response_code(400);
        echo json_encode(["message" => "Phone number required."]);
        exit();
    }
    
    $query = "SELECT * FROM loyalty_users WHERE phone = :phone LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute(['phone' => $phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode($user);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "User not found."]);
    }
}

// --- DEFAULT ROUTE ---
else {
    error_log("Route not found: " . $uri);
    http_response_code(404);
    echo json_encode([
        "error" => "Route not found",
        "uri" => $uri,
        "method" => $method,
        "available_routes" => [
            "GET /api/plates",
            "GET /api/plates/{id}",
            "GET /api/images/{filename}",
            "POST /api/review",
            "POST /api/order",
            "POST /api/reservation",
            "GET /api/loyalty",
            "POST /api/admin/login",
            "POST /api/admin/verify-otp",
            "POST /api/admin/logout",
            "GET /api/admin/orders",
            "GET /api/admin/orders/stats",
            "GET /api/admin/orders/{id}",
            "PUT /api/admin/orders/{id}?action=status",
            "GET /api/admin/menu",
            "GET /api/admin/menu/stats",
            "GET /api/admin/menu/{id}",
            "POST /api/admin/menu",
            "PUT /api/admin/menu/{id}",
            "PUT /api/admin/menu/{id}?action=toggle",
            "DELETE /api/admin/menu/{id}",
            "GET /api/admin/reservations",
            "GET /api/admin/reservations/stats",
            "GET /api/admin/reservations/{id}",
            "POST /api/admin/reservations",
            "PUT /api/admin/reservations/{id}",
            "PUT /api/admin/reservations/{id}?action=status",
            "DELETE /api/admin/reservations/{id}"
        ]
    ]);
}
?>
