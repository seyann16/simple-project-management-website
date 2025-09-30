<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Error reporting - turn off for production
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not authenticated"]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// GET - Get all projects for user
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["success" => true, "projects" => $projects]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error fetching projects"]);
    }
    exit;
}

// POST - Create new project
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit;
    }
    
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    
    if (empty($title)) {
        echo json_encode(["success" => false, "message" => "Project title is required"]);
        exit;
    }
    
    try {
        $query = "INSERT INTO projects (user_id, title, description) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$_SESSION['user_id'], $title, $description])) {
            echo json_encode(["success" => true, "message" => "Project created successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to create project"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
    exit;
}

// PUT - Update project status
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit;
    }
    
    $id = intval($input['id'] ?? 0);
    $status = $input['status'] ?? '';
    
    if (!in_array($status, ['pending', 'in progress', 'completed'])) {
        echo json_encode(["success" => false, "message" => "Invalid status"]);
        exit;
    }
    
    try {
        $query = "UPDATE projects SET status = ? WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$status, $id, $_SESSION['user_id']])) {
            echo json_encode(["success" => true, "message" => "Project updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update project"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
    exit;
}

// DELETE - Delete project
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit;
    }
    
    $id = intval($input['id'] ?? 0);
    
    try {
        $query = "DELETE FROM projects WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$id, $_SESSION['user_id']])) {
            echo json_encode(["success" => true, "message" => "Project deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete project"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
    exit;
}

// If no method matched
echo json_encode(["success" => false, "message" => "Invalid request method"]);
?>