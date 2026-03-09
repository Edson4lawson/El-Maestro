<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Debug: Log all requests
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

include_once './config/database.php';
include_once './models/Plate.php';
include_once './models/Order.php';
include_once './models/Reservation.php';

$database = new Database();
$db = $database->getConnection();

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
    $imagePath = __DIR__ . '/../../frontend/src/assets/' . basename($imageFile);
    
    if (!file_exists($imagePath)) {
        http_response_code(404);
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
elseif ($method === 'GET' && (isRoute($uri, 'plates') || $uri === '/api' || $uri === '/api/' || $uri === '/api/index.php')) {
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
elseif ($method === 'POST' && isRoute($uri, 'admin/login')) {
    include_once './controllers/AuthController.php';
    $controller = new AuthController();
    $controller->login();
}

// --- POST ADMIN VERIFY OTP ---
elseif ($method === 'POST' && isRoute($uri, 'admin/verify-otp')) {
    include_once './controllers/AuthController.php';
    $controller = new AuthController();
    $controller->verifyOTP();
}

// --- POST ADMIN LOGOUT ---
elseif ($method === 'POST' && isRoute($uri, 'admin/logout')) {
    include_once './controllers/AuthController.php';
    $controller = new AuthController();
    $controller->logout();
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
            "POST /api/admin/logout"
        ]
    ]);
}
?>
