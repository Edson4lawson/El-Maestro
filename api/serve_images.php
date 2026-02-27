<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Get the image filename from the query parameter
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
    echo json_encode(["error" => "Image not found", "path" => $imagePath]);
    exit;
}

// Get the MIME type
$mimeType = mime_content_type($imagePath);

// Serve the image
header("Content-Type: $mimeType");
header("Cache-Control: public, max-age=31536000"); // Cache for 1 year
readfile($imagePath);
?>
