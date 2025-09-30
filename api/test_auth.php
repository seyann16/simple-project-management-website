<?php
echo "<h2>Project Structure Test</h2>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Root URL: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br>";

// Test if api/auth.php exists
$api_path = __DIR__ . 'api/auth.php';
if (file_exists($api_path)) {
    echo "✅ api/auth.php exists at: " . $api_path . "<br>";
} else {
    echo "❌ api/auth.php NOT FOUND at: " . $api_path . "<br>";
}

// Test if config/database.php exists  
$config_path = __DIR__ . '/config/database.php';
if (file_exists($config_path)) {
    echo "✅ config/database.php exists at: " . $config_path . "<br>";
} else {
    echo "❌ config/database.php NOT FOUND at: " . $config_path . "<br>";
}
?>