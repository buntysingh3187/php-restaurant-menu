<?php
// place order page for tables
// Database connection
$host = 'localhost';
$port = 3307;
$user = 'root';
$password = '';
$database = 'restaurant_menu';

// Database connection
$setup_needed = false;
mysqli_report(MYSQLI_REPORT_OFF);
try {
    $conn = new mysqli($host, $user, $password, $database, $port);

    if ($conn->connect_error) {
        $setup_needed = true;
    } else {
        $table_id = isset($_POST['table_id']) ? intval($_POST['table_id']) : 0;
        $quantities = isset($_POST['quantity']) ? array_filter($_POST['quantity'], function($q) {
            return is_numeric($q) && $q > 0;
        }) : [];

        // Insert orders into the database
        if (empty($quantities)) {
            header("Location: menu.php?table_id=$table_id&error=Invalid order quantities");
            exit;
        }

        foreach ($quantities as $menu_item_id => $quantity) {
            if ($quantity > 0) {
                $stmt = $conn->prepare("INSERT INTO orders (table_id, menu_item_id, quantity, status) VALUES (?, ?, ?, 'Pending')");
                $stmt->bind_param("iii", $table_id, $menu_item_id, $quantity);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Set table status to Busy after placing an order
        $conn->query("UPDATE tables SET status = 'Busy' WHERE id = $table_id");

        // Redirect to order status page
        header("Location: order_status.php?table_id=$table_id");
        $conn->close();
        exit;
    }
} catch (Exception $e) {
    $setup_needed = true;
}

if ($setup_needed):
?>
<!-- place order html below -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Error</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div style="width:100%;text-align:center;padding:40px;">
        <h2>Wait please, fixing error...</h2>
    </div>
</body>
</html>
<?php endif; ?>

