<?php
// setup file for restaurant project

// Auto-setup database if not exists
$host = 'localhost';
$port = 3307;
$user = 'root';
$password = '';
$database = 'restaurant_menu';

$conn = new mysqli($host, $user, $password, '', $port);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected to MySQL successfully.<br>";
}

// Create database if it doesn't exist
if ($conn->query("CREATE DATABASE IF NOT EXISTS $database") === TRUE) {
    echo "Database '$database' created or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Use the database
$conn->select_db($database);

$conn->query("SET FOREIGN_KEY_CHECKS=0");
$conn->query("DROP TABLE IF EXISTS orders");
$conn->query("DROP TABLE IF EXISTS menu_items");
$conn->query("DROP TABLE IF EXISTS categories");
$conn->query("DROP TABLE IF EXISTS tables");
$conn->query("SET FOREIGN_KEY_CHECKS=1");

// Create tables if they don't exist
$conn->query("

CREATE TABLE IF NOT EXISTS tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number INT NOT NULL UNIQUE,
    status VARCHAR(50) DEFAULT 'Available'
)");

$conn->query("
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
)");

$conn->query("
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT,
    image_url VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
)");

$conn->query("
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (table_id) REFERENCES tables(id),
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
)");

echo "Database setup completed successfully.<br>";

$sampleTables = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
foreach ($sampleTables as $num) {
    $conn->query("INSERT IGNORE INTO tables (table_number) VALUES ($num)");
}
echo "Sample tables inserted.<br>";

// Insert sample categories
$sampleCategories = ['Main Course', 'Starters', 'Drinks', 'Desserts'];
$categoryIds = [];
foreach ($sampleCategories as $cat) {
    $conn->query("INSERT IGNORE INTO categories (name) VALUES ('" . $conn->real_escape_string($cat) . "')");
}
$res = $conn->query("SELECT id, name FROM categories");
while ($row = $res->fetch_assoc()) {
    $categoryIds[$row['name']] = $row['id'];
}
echo "Sample categories inserted.<br>";

// Insert sample menu items
$sampleMenu = [
    // name, price, category, image_url (local path)
    ['Burger', 120.00, 'Main Course', 'images/burger.jpg'],
    ['Pizza', 250.00, 'Main Course', 'images/pizza.jpg'],
    ['Pasta', 180.00, 'Main Course', 'images/pasta.jpg'],
    ['French Fries', 90.00, 'Starters', 'images/fries.jpg'],
    ['Cold Drink', 60.00, 'Drinks', 'images/drink.jpg'],
    ['Salad', 80.00, 'Starters', 'images/salad.jpg'],
    ['Soup', 70.00, 'Starters', 'images/soup.jpg'],
    ['Sandwich', 110.00, 'Main Course', 'images/sandwich.jpg'],
    ['Coffee', 50.00, 'Drinks', 'images/coffee.jpg'],
    ['Ice Cream', 75.00, 'Desserts', 'images/icecream.jpg']
];
foreach ($sampleMenu as $item) {
    $name = $conn->real_escape_string($item[0]);
    $price = $item[1];
    $catName = $item[2];
    $catId = isset($categoryIds[$catName]) ? $categoryIds[$catName] : 'NULL';
    $image_url = $conn->real_escape_string($item[3]);
    $conn->query("INSERT IGNORE INTO menu_items (name, price, category_id, image_url) VALUES ('$name', $price, $catId, '$image_url')");
}
echo "Sample menu items inserted.<br>";

// Insert sample orders
$sampleOrders = [
    // table_id, menu_item_id, quantity, status
    [1, 1, 2, 'Pending'],
    [1, 2, 1, 'Pending'],
    [2, 3, 3, 'Done'],
    [2, 4, 1, 'Done'],
    [3, 5, 2, 'Pending'],
    [4, 6, 1, 'Pending'],
    [5, 7, 2, 'Done'],
    [5, 8, 1, 'Pending'],
    [6, 9, 2, 'Pending'],
    [7, 10, 1, 'Pending']
];
foreach ($sampleOrders as $order) {
    $conn->query("INSERT INTO orders (table_id, menu_item_id, quantity, status) VALUES ($order[0], $order[1], $order[2], '$order[3]')");
}
echo "Sample orders inserted.<br>";

$conn->close();
// $conn = null; // just in case
?>
