<?php
// Headers FIRST - before any output
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Start session AFTER headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Include database connection
require_once '../config/database.php';

// Get JSON input for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If no JSON input, try form data
    if (!$input) {
        $input = $_POST;
    }
    
    $action = $input['action'] ?? '';
    
    if ($action === 'register') {
        registerUser($input);
    } elseif ($action === 'login') {
        loginUser($input);
    } elseif ($action === 'logout') {
        logoutUser();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'check_login') {
        checkLoginStatus();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        exit;
    }
}

function registerUser($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        echo json_encode(["success" => false, "message" => "Database connection failed"]);
        exit;
    }
    
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
        exit;
    }
    
    try {
        // Check if user exists
        $checkQuery = "SELECT id FROM users WHERE username = ? OR email = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$username, $email]);
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode(["success" => false, "message" => "Username or email already exists"]);
            exit;
        }
        
        // Create new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertQuery = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $insertStmt = $db->prepare($insertQuery);
        
        if ($insertStmt->execute([$username, $email, $hashedPassword])) {
            echo json_encode(["success" => true, "message" => "Registration successful!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Registration failed"]);
        }
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Database error occurred"]);
    }
    exit;
}

function loginUser($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        echo json_encode(["success" => false, "message" => "Database connection failed"]);
        exit;
    }
    
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Username and password are required"]);
        exit;
    }
    
    try {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo json_encode(["success" => true, "message" => "Login successful!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid credentials"]);
        }
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Database error occurred"]);
    }
    exit;
}

function logoutUser() {
    session_destroy();
    echo json_encode(["success" => true, "message" => "Logged out successfully"]);
    exit;
}

function checkLoginStatus() {
    if (isset($_SESSION['user_id'])) {
        echo json_encode(["logged_in" => true, "username" => $_SESSION['username']]);
    } else {
        echo json_encode(["logged_in" => false]);
    }
    exit;
}

// If nothing matched
echo json_encode(["success" => false, "message" => "Invalid request"]);
?>