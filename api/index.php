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
    return str_contains($uri, $path);
}

// --- GET PLATES ---
if ($method === 'GET' && (isRoute($uri, 'plates') || $uri === '/api' || $uri === '/api/')) {
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
    $stmt->bindParam(":phone", $phone);
    $stmt->execute();
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row);
    } else {
        // Create auto if doesn't exist for demo/user experience
        $insert = "INSERT INTO loyalty_users (phone, points, tier) VALUES (:phone, 100, 'Bronze')";
        $stmt_in = $db->prepare($insert);
        $stmt_in->bindParam(":phone", $phone);
        $stmt_in->execute();
        
        echo json_encode(["phone" => $phone, "points" => 100, "tier" => "Bronze"]);
    }
}

// --- SERVE IMAGES ---
elseif ($method === 'GET' && isRoute($uri, 'images')) {
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
    $imagePath = __DIR__ . '/../src/assets/' . basename($imageFile);
    
    if (!file_exists($imagePath)) {
        http_response_code(404);
        echo json_encode(["error" => "Image not found"]);
        exit;
    }
    
    // Get the MIME type
    $mimeType = mime_content_type($imagePath);
    
    // Serve the image
    header("Content-Type: $mimeType");
    header("Cache-Control: public, max-age=31536000"); // Cache for 1 year
    readfile($imagePath);
}

else {
    http_response_code(404);
    echo json_encode(["message" => "Route not found."]);
}
?>

