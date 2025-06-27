<?php
// tables api for restaurant project

// Database connection
$host = 'localhost:3307';
$user = 'root';
$password = '';
$database = 'restaurant_menu';

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tables
$sql = "SELECT * FROM tables";
// get all tables
$result = $conn->query($sql);

$tables = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tables[] = $row;
    }
}

// Return tables as JSON
header('Content-Type: application/json');
echo json_encode($tables);


$conn->close();

?>