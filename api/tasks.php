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

// GET - Get all tasks for user's projects
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT t.*, p.title as project_title 
                  FROM tasks t 
                  JOIN projects p ON t.project_id = p.id 
                  WHERE p.user_id = ? 
                  ORDER BY t.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["success" => true, "tasks" => $tasks]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error fetching tasks"]);
    }
    exit;
}

// POST - Create new task
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit;
    }
    
    $project_id = intval($input['project_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $priority = $input['priority'] ?? 'medium';
    $due_date = $input['due_date'] ?? null;
    
    // Debug logging
    error_log("Received due_date: " . var_export($due_date, true));
    
    if (empty($title) || $project_id === 0) {
        echo json_encode(["success" => false, "message" => "Title and project are required"]);
        exit;
    }
    
    if (!in_array($priority, ['low', 'medium', 'high'])) {
        $priority = 'medium';
    }
    
    // Handle empty due date string
    if ($due_date === '') {
        $due_date = null;
    }
    
    // Validate due_date format if provided
    if ($due_date !== null && $due_date !== '') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $due_date)) {
            echo json_encode(["success" => false, "message" => "Invalid date format. Use YYYY-MM-DD"]);
            exit;
        }
    } else {
        $due_date = null; // Ensure it's properly null
    }
    
    // Verify project belongs to user
    try {
        $checkQuery = "SELECT id FROM projects WHERE id = ? AND user_id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$project_id, $_SESSION['user_id']]);
        
        if ($checkStmt->rowCount() === 0) {
            echo json_encode(["success" => false, "message" => "Invalid project"]);
            exit;
        }
        
        $query = "INSERT INTO tasks (project_id, title, description, priority, due_date) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        error_log("Executing query with due_date: " . var_export($due_date, true));
        
        if ($stmt->execute([$project_id, $title, $description, $priority, $due_date])) {
            echo json_encode(["success" => true, "message" => "Task created successfully"]);
        } else {
            $error = $stmt->errorInfo();
            error_log("Database error: " . $error[2]);
            echo json_encode(["success" => false, "message" => "Failed to create task: " . $error[2]]);
        }
    } catch (Exception $e) {
        error_log("Exception: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

// PUT - Update task status
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
        $query = "UPDATE tasks t 
                  JOIN projects p ON t.project_id = p.id 
                  SET t.status = ? 
                  WHERE t.id = ? AND p.user_id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$status, $id, $_SESSION['user_id']])) {
            echo json_encode(["success" => true, "message" => "Task updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update task"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
    exit;
}

// DELETE - Delete task
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit;
    }
    
    $id = intval($input['id'] ?? 0);
    
    try {
        $query = "DELETE tasks 
                  FROM tasks 
                  JOIN projects ON tasks.project_id = projects.id 
                  WHERE tasks.id = ? AND projects.user_id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$id, $_SESSION['user_id']])) {
            echo json_encode(["success" => true, "message" => "Task deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete task"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
    exit;
}

// If no method matched
echo json_encode(["success" => false, "message" => "Invalid request method"]);
?>