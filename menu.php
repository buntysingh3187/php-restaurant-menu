<?php
// menu page for restaurant
// Database connection
$host = 'localhost';
$port = 3307;
$user = 'root';
$password = '';
$database = 'restaurant_menu';

$setup_needed = false;
$menu_items = [];
$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;


mysqli_report(MYSQLI_REPORT_OFF);
// hope this connects
try {
    $conn = new mysqli($host, $user, $password, $database, $port);

    if ($conn->connect_error) {
        $setup_needed = true;
    } else {
        // Fetch menu items
        $sql = "SELECT * FROM menu_items";
        $result = $conn->query($sql);
        if (!$result) {
            $setup_needed = true;
        } else {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $menu_items[] = $row;
                }
            }
        }
    }
} catch (Exception $e) {
    $setup_needed = true;
}

?>
<!-- menu html below -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Restaurant</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .menu-header {
            background: #e9f5e1;
            padding: 18px 0 10px 0;
            border-radius: 10px 10px 0 0;
            margin-bottom: 0;
        }
        .menu-container {
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
    <div class="menu-header">
        <h1>Menu</h1>
        <h2>Table: <?php echo htmlspecialchars($table_id); ?></h2>
    </div>
    <div class="menu-container">
        <?php if ($setup_needed): ?>
            <div style="text-align:center; padding:40px;">
                <h2>Wait please, fixing error...</h2>
            </div>
        <?php else: ?>
        <form action="place_order.php" method="POST" id="orderForm">
            <input type="hidden" name="table_id" value="<?php echo htmlspecialchars($table_id); ?>">
<?php
$categories = [];
foreach ($menu_items as $item) {
    $cat = $item['category'] ?? 'Other';
    if (!isset($categories[$cat])) {
        $categories[$cat] = [];
    }
    $categories[$cat][] = $item;
}
?>
            <div id="menu">
                <?php foreach ($categories as $cat => $items): ?>
                    <?php if (count($items) > 0): ?>
                        <h3 style="margin-top:28px; margin-bottom:12px; text-align:left;"><?php echo htmlspecialchars($cat); ?></h3>
                        <div class="menu-grid">
                        <?php foreach ($items as $item): ?>
                            <div class="menu-item-card">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/90x70?text=Food'); ?>" alt="Food Image" style="border-radius:8px; margin-bottom:10px; width:90px; height:70px; object-fit:cover;">
                                <div class="menu-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="menu-item-price">&#8377;<?php echo htmlspecialchars($item['price']); ?></div>
                                <input type="number" name="quantity[<?php echo $item['id']; ?>]" min="0" placeholder="Quantity" class="menu-item-qty">
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div style="display:flex; flex-direction:column; align-items:center; margin-top:24px;">
                <button type="submit" style="margin-bottom:10px;">Place Order</button>
                <span id="order-feedback" class="badge badge-done" style="display:none;">Order placed!</span>
                <a href="order_status.php?table_id=<?php echo htmlspecialchars($table_id); ?>" class="btn" style="margin-top:10px;">View Order Status</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
    <script>
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        setTimeout(function() {
            document.getElementById('order-feedback').style.display = 'inline';
            setTimeout(function() {
                document.getElementById('order-feedback').style.display = 'none';
            }, 2000);
        }, 100);
    });
    </script>
</body>
</html>

<?php
$conn->close();
// this breaks for some reason
// $conn = null; // just in case
?>
