<?php
// chef dashboard file (for chef only and rat's too) 

// Database connection
$host = 'localhost';
$port = 3307;
$user = 'root';
$password = '';
$database = 'restaurant_menu';

$setup_needed = false;
$orders = [];

mysqli_report(MYSQLI_REPORT_OFF);
// just work 
try {
    $conn = new mysqli($host, $user, $password, $database, $port);

    if ($conn->connect_error) {
        $setup_needed = true;
    } else {
        // Fetch pending orders
        $sql = "SELECT o.id AS order_id, o.table_id, t.table_number, m.name AS menu_item, o.quantity, o.status
                FROM orders o
                LEFT JOIN tables t ON o.table_id = t.id
                JOIN menu_items m ON o.menu_item_id = m.id
                WHERE o.status = 'Pending'
                ORDER BY o.created_at ASC, o.id ASC";
        $result = $conn->query($sql);

        if (!$result) {
            $setup_needed = true;
        } else {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $orders[] = $row;
                }
            }
        }
    }
} catch (Exception $e) {
    $setup_needed = true;
}

// AJAX response for polling
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    ob_start();
    if (count($orders) > 0) {
        foreach ($orders as $order) {
            echo "<div class='order'>";
            echo "<p>Table: " . htmlspecialchars($order['table_number']) . "</p>";
            echo "<p>Item: " . htmlspecialchars($order['menu_item']) . "</p>";
            echo "<p>Quantity: " . htmlspecialchars($order['quantity']) . "</p>";
            echo "<form method='POST'>";
            echo "<input type='hidden' name='order_id' value='" . $order['order_id'] . "'>";
            echo "<button type='submit'>Mark as Done</button>";
            echo "</form>";
            echo "</div>";
        }
    } else {
        echo "<p>No pending orders.</p>";
    }
    $conn->close();
    echo ob_get_clean();
    exit;
}

// Mark order as done
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $update_sql = "UPDATE orders SET status = 'Done' WHERE id = $order_id";

    // Get the table_id for this order
    $table_id_res = $conn->query("SELECT table_id, status FROM orders WHERE id = $order_id");
    $table_id_row = $table_id_res ? $table_id_res->fetch_assoc() : null;
    $table_id_val = $table_id_row ? intval($table_id_row['table_id']) : 0;
    $order_status_val = $table_id_row ? $table_id_row['status'] : '';

    // Mark this order item as done
    $conn->query($update_sql);

    // For dine-in, update table status if all items are done
    if ($table_id_val > 0) {
        $check_sql = "SELECT COUNT(*) AS pending_items
                      FROM orders
                      WHERE table_id = $table_id_val
                      AND status = 'Pending'";
        $check_result = $conn->query($check_sql);
        $row = $check_result->fetch_assoc();

        if ($row['pending_items'] == 0) {
            $update_table_sql = "UPDATE tables SET status = 'Completed'
                                 WHERE id = $table_id_val";
            $conn->query($update_table_sql);
        }
    }
    // For to-go, do nothing extra (each item is marked done individually)
    header("Location: chef_dashboard.php");
    exit;
}
?>
<!-- chef dashboard html below -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chef Dashboard - Restaurant</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .chef-header {
            background: #e9f5e1;
            padding: 18px 0 10px 0;
            border-radius: 10px 10px 0 0;
            margin-bottom: 0;
        }
        .chef-container {
            background: #fff;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            padding: 24px;
            max-width: 700px;
            margin: 0 auto 24px auto;
        }
    </style>
</head>
<body>
    <div class="chef-header">
        <h1>Chef Dashboard</h1>
    </div>
    <div class="chef-container">
        <div id="orders">
            <?php if ($setup_needed): ?>
                <div class="chef-error-message">
                    <h2>Wait please, fixing error...</h2>
                </div>
            <?php elseif (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order">
                        <p>Table: <?php echo htmlspecialchars($order['table_number']); ?></p>
                        <p>Item: <?php echo htmlspecialchars($order['menu_item']); ?></p>
                        <p>Quantity: <?php echo htmlspecialchars($order['quantity']); ?></p>
                        <form method="POST" class="mark-done-form">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <button type="submit">Mark as Done</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No pending orders.</p>
            <?php endif; ?>
        </div>
    </div>
    <style>
    .chef-error-message {
        width:100%;
        text-align:center;
        padding:40px;
        color:#c62828;
        background:#fff3e0;
        border-radius:8px;
        margin-bottom:18px;
    }
    </style>
    <script src="scripts.js"></script>
    <script>
    // AJAX polling 
    setInterval(function() {
        var url = window.location.href;
        if (url.indexOf('?') === -1) url += '?';
        if (url.indexOf('ajax=1') === -1) url += (url.endsWith('?') ? '' : '&') + 'ajax=1';
        fetch(url)
            .then(res => res.text())
            .then(html => {
                var temp = document.createElement('div');
                temp.innerHTML = html;
                var orders = temp.querySelector('#orders');
                if (orders && document.getElementById('orders')) {
                    document.getElementById('orders').innerHTML = orders.innerHTML;
                }
            });
    }, 3000);
    </script>
</body>
</html>

<?php
$conn->close();
// this breaks for some reason
// $conn = null; // not needed but just in case
?>
