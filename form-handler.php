<?php
// Debugging code
file_put_contents('debug_log.txt', 'Request method: ' . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);


// Start the session
session_start();

// Enable error reporting for debugging (remove this in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validate the request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    // Option 1: Display a friendly message
    echo "This page is only accessible via form submission.";
    exit;

    //Option 2: Redirect to the form page
    header('Location: /index.html');
    exit;


    // Validate the request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Invalid request method.']);
    exit;
}

// Validate the CSRF token (assuming you've generated one)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a token if not already set
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403); // Forbidden
    echo json_encode(['message' => 'Invalid CSRF token.']);
    exit;
}

// Sanitize and validate the input data
$name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$email = filter_var($email, FILTER_VALIDATE_EMAIL);
$phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
$terms = isset($_POST['terms']) ? true : false;

// Remove non-digit characters from the phone number
$phone = preg_replace('/[^0-9]/', '', $phone);

// Validate the phone number format (expecting 10 digits)
if (!preg_match('/^[0-9]{10}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid phone number format.']);
    exit;
}

// Check if all required fields are filled out
if (empty($name)) {
    http_response_code(400);
    echo json_encode(['message' => 'Name is required.']);
    exit;
}
if (empty($email)) {
    http_response_code(400);
    echo json_encode(['message' => 'Email is required.']);
    exit;
}
if (empty($phone)) {
    http_response_code(400);
    echo json_encode(['message' => 'Phone is required.']);
    exit;
}

// Validate email format
if (!$email) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid email format.']);
    exit;
}

// Check if the terms checkbox is ticked
if (!$terms) {
    http_response_code(400);
    echo json_encode(['message' => 'You must agree to the terms and conditions.']);
    exit;
}

// Establish a database connection (e.g., using PDO)
try {
    $db = new PDO('mysql:host=localhost;dbname=cheetosdb', 'cheetos_user', 'HyunLee');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log($e->getMessage()); // Log the error message
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Database connection error.']);
    exit;
}

// Store the data in the database
try {
    $stmt = $db->prepare('INSERT INTO submissions (name, email, phone) VALUES (:name, :email, :phone)');
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->execute();
    http_response_code(200); // OK
    echo json_encode(['message' => 'Submission successful.']);
} catch (PDOException $e) {
    error_log($e->getMessage()); // Log the error message
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Failed to store data.']);
    exit;
}
?>
