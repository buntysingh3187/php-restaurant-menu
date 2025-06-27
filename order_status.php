<?php
// order status page for tables

// Database connection
$host = 'localhost';
$port = 3307;
$user = 'root';
$password = '';
$database = 'restaurant_menu';

// Database connection
$setup_needed = false;
$orders = [];
$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

mysqli_report(MYSQLI_REPORT_OFF);
try {
    $conn = new mysqli($host, $user, $password, $database, $port);

    if ($conn->connect_error) {
        $setup_needed = true;
    } else {
        // Fetch orders for the table
        $sql = "SELECT m.name AS menu_item, o.quantity, o.status
                FROM orders o
                JOIN menu_items m ON o.menu_item_id = m.id
                WHERE o.table_id = $table_id";
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

// Calculate order progress
$total_items = count($orders);
$completed_items = count(array_filter($orders, function ($order) {
    return $order['status'] === 'Done';
}));

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    ob_start();
    // Progress bar section
    echo '<div id="progress-bar-section">';
    echo '<div class="progress-bar">';
    echo '<div class="progress-bar-inner" id="progress-bar-inner" style="width:' . ($total_items > 0 ? round(($completed_items/$total_items)*100) : 0) . '%"></div>';
    echo '</div>';
    echo '<p id="progress-bar-text">';
    echo $completed_items . ' of ' . $total_items . ' items completed. ';
    if ($completed_items === $total_items && $total_items > 0) {
        echo '<span class="badge badge-done">Order Complete</span>';
    } else {
        echo '<span class="badge badge-pending">In Progress</span>';
    }
    echo '</p>';
    echo '</div>';
    // Orders section
    echo '<div id="orders">';
    if (count($orders) > 0) {
        foreach ($orders as $order) {
            echo "<div class='order'>";
            echo "<p>Item: " . htmlspecialchars($order['menu_item']) . "</p>";
            echo "<p>Quantity: " . htmlspecialchars($order['quantity']) . "</p>";
            echo "<p>Status: ";
            if ($order['status'] === 'Done') {
                echo "<span class='badge badge-done' style='display:inline-block;'>Done</span>";
            } else {
                echo "<span class='badge badge-pending' style='display:inline-block;'>Pending</span>";
            }
            echo "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>No orders found for this table.</p>";
    }
    echo '</div>';
    $conn->close();
    echo ob_get_clean();
    exit;
}
?>
<!-- order status html below -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status - Restaurant</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .status-header {
            background: #e9f5e1;
            padding: 18px 0 10px 0;
            border-radius: 10px 10px 0 0;
            margin-bottom: 0;
        }
        .status-container {
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
    <div class="status-header">
        <h1>Order Status</h1>
        <h2>Table: <?php echo htmlspecialchars($table_id); ?></h2>
    </div>
    <div class="status-container">
        <?php if ($setup_needed): ?>
            <div style="width:100%;text-align:center;padding:40px;">
                <h2>Wait please, fixing error...</h2>
            </div>
        <?php else: ?>
        <div id="progress-bar-section">
            <div class="progress-bar">
                <div class="progress-bar-inner" id="progress-bar-inner" style="width:<?php echo $total_items > 0 ? round(($completed_items/$total_items)*100) : 0; ?>%"></div>
            </div>
            <p id="progress-bar-text">
                <?php echo $completed_items; ?> of <?php echo $total_items; ?> items completed.
                <?php if ($completed_items === $total_items && $total_items > 0): ?>
                    <span class="badge badge-done">Order Complete</span>
                <?php else: ?>
                    <span class="badge badge-pending">In Progress</span>
                <?php endif; ?>
            </p>
        </div>
        <div id="orders">
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order">
                        <p>Item: <?php echo htmlspecialchars($order['menu_item']); ?></p>
                        <!-- If price is available, show it -->
                        <?php if (isset($order['price'])): ?>
                            <p>Price: &#8377;<?php echo htmlspecialchars($order['price']); ?></p>
                        <?php endif; ?>
                        <p>Quantity: <?php echo htmlspecialchars($order['quantity']); ?></p>
                        <p>Status:
                            <?php if ($order['status'] === 'Done'): ?>
                                <span class="badge badge-done" style="display:inline-block;">Done</span>
                            <?php else: ?>
                                <span class="badge badge-pending" style="display:inline-block;">Pending</span>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No orders found for this table.</p>
            <?php endif; ?>
        </div>
        <br>
        <script>
        // AJAX polling for live progress bar and order updates
        setInterval(function() {
            var url = window.location.href;
            if (url.indexOf('?') === -1) url += '?';
            if (url.indexOf('ajax=1') === -1) url += (url.endsWith('?') ? '' : '&') + 'ajax=1';
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    // Extract orders and progress bar from response
                    var temp = document.createElement('div');
                    temp.innerHTML = html;
                    var orders = temp.querySelector('#orders');
                    var progressBarSection = temp.querySelector('#progress-bar-section');
                    if (orders && document.getElementById('orders')) {
                        document.getElementById('orders').innerHTML = orders.innerHTML;
                    }
                    if (progressBarSection && document.getElementById('progress-bar-section')) {
                        document.getElementById('progress-bar-section').innerHTML = progressBarSection.innerHTML;
                    }
                });
        }, 3000);
        </script>
        <a href="menu.php?table_id=<?php echo htmlspecialchars($table_id); ?>" class="btn" style="display:inline-block; margin-top:16px;">Add More Items</a>
        <?php endif; ?>
    </div>
    <script src="scripts.js"></script>
</body>
</html>

<?php
$conn->close();
// this breaks for some reason
// $conn = null; // just in case
?>
