<?php
session_start();
$show_dashboard = false;
$error = '';

$ADMIN_USER = 'admin';
$ADMIN_PASS = 'admin123';

if (isset($_POST['admin_user']) && isset($_POST['admin_pass'])) {
    if ($_POST['admin_user'] === $ADMIN_USER && $_POST['admin_pass'] === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $show_dashboard = true;
    } else {
        $error = 'Invalid username or password.';
    }
} elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $show_dashboard = true;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}
?>
<!-- i should probably make this look better -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-container {
            max-width: 820px;
            margin: 36px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(56,142,60,0.10);
            padding: 32px 28px 32px 28px;
        }
        .admin-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .admin-tabs {
            display: flex;
            gap: 18px;
            margin-bottom: 24px;
            justify-content: center;
        }
        .admin-tab {
            padding: 10px 18px;
            border-radius: 8px;
            background: #e9f5e1;
            cursor: pointer;
            font-weight: 600;
            color: #2e7031;
            border: none;
            transition: background 0.18s, color 0.18s;
        }
        .admin-tab.active {
            background: #388e3c;
            color: #fff;
        }
        .logout-link {
            float: right;
            margin-top: -32px;
        }
        .setup-btn {
            margin-top: 18px;
        }
        .admin-tab-content h2, .admin-tab-content h3 {
            color: #2e7031;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .admin-tab-content form {
            background: #f8faf7;
            border-radius: 8px;
            padding: 18px 16px 10px 16px;
            margin-bottom: 18px;
            box-shadow: 0 1px 4px rgba(56,142,60,0.05);
        }
        .admin-tab-content table {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(56,142,60,0.05);
            margin-bottom: 18px;
        }
        .admin-tab-content th, .admin-tab-content td {
            padding: 10px 8px;
            text-align: left;
        }
        .admin-tab-content th {
            background: #e9f5e1;
            color: #2e7031;
            font-weight: 600;
        }
        .admin-tab-content tr:nth-child(even) {
            background: #f5ecd7;
        }
        .admin-tab-content tr:nth-child(odd) {
            background: #fff;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <?php if ($show_dashboard): ?>
            <a href="admin.php?logout=1" class="btn logout-link">Logout</a>
        <?php endif; ?>
    </div>
    <?php if (!$show_dashboard): ?>
        <form method="POST" style="max-width:320px;margin:0 auto;">
            <h2>Admin Login</h2>
            <?php if ($error): ?>
                <div style="color:red;margin-bottom:10px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <input type="text" name="admin_user" placeholder="Username" required style="width:100%;padding:8px;margin-bottom:12px;">
            <input type="password" name="admin_pass" placeholder="Password" required style="width:100%;padding:8px;margin-bottom:12px;">
            <button type="submit" class="btn" style="width:100%;">Login</button>
        </form>
    <?php else: ?>
        <div class="admin-tabs">
            <div class="admin-tab active" onclick="showTab('menu')">Menu Management</div>
            <div class="admin-tab" onclick="showTab('tables')">Table Management</div>
            <div class="admin-tab" onclick="showTab('setup')">Setup Database</div>
        </div>
        <div id="tab-menu" class="admin-tab-content">
            <h2>Menu Management</h2>
            <div id="category-management-content" style="margin-bottom:32px;">
                <?php
                // so much to type 
                // $cat_msg = "debug";
                $cat_msg = '';
                $host = 'localhost';
                $port = 3307;
                $user = 'root';
                $password = '';
                $database = 'restaurant_menu';
                $cat_conn = new mysqli($host, $user, $password, $database, $port);
                if (isset($_POST['add_category']) && !empty(trim($_POST['category_name']))) {
                    $cat_name = trim($_POST['category_name']);
                    $stmt = $cat_conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
                    $stmt->bind_param("s", $cat_name);
                    if ($stmt->execute()) {
                        $cat_msg = "Category added!";
                    } else {
                        $cat_msg = "Error adding category.";
                    }
                    $stmt->close();
                }
                // Fetch categories
                $categories = [];
                $cat_result = $cat_conn->query("SELECT * FROM categories ORDER BY name ASC");
                if ($cat_result && $cat_result->num_rows > 0) {
                    while ($row = $cat_result->fetch_assoc()) {
                        $categories[] = $row;
                    }
                }
                ?>
                <form method="POST" style="margin-bottom:18px;display:flex;gap:12px;align-items:center;">
                    <input type="text" name="category_name" placeholder="New Category" required style="padding:8px;width:180px;">
                    <button type="submit" name="add_category" class="btn">Add Category</button>
                    <?php if ($cat_msg): ?>
                        <span style="color:green;"><?php echo htmlspecialchars($cat_msg); ?></span>
                    <?php endif; ?>
                </form>
                <div style="margin-bottom:10px;">
                    <b>Categories:</b>
                    <?php foreach ($categories as $cat): ?>
                        <span class="badge badge-pending" style="margin-right:6px;"><?php echo htmlspecialchars($cat['name']); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php $cat_conn->close(); ?>
            </div>
            <div id="menu-management-content">
                <?php
                // Menu CRUD logic with error handling
                // menu stuff starts here
                $menu_msg = '';
                $db_error = false;
                // $menu_msg = "test"; // for debug remove later
                $host = 'localhost';
                $port = 3307;
                $user = 'root';
                $password = '';
                $database = 'restaurant_menu';
                mysqli_report(MYSQLI_REPORT_OFF);
                try {
                    $conn = new mysqli($host, $user, $password, $database, $port);

                    if ($conn->connect_error) {
                        $db_error = true;
                    } else {
                        // Handle add menu item
                        if (isset($_POST['add_menu'])) {
                            $name = trim($_POST['menu_name']);
                            $price = floatval($_POST['menu_price']);
                            $category_id = intval($_POST['menu_category_id']);
                            $image_url = '';
                            if (isset($_FILES['menu_image']) && $_FILES['menu_image']['tmp_name']) {
                                $target_dir = "uploads/";
                                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                                $filename = uniqid() . "_" . basename($_FILES["menu_image"]["name"]);
                                $target_file = $target_dir . $filename;
                                if (move_uploaded_file($_FILES["menu_image"]["tmp_name"], $target_file)) {
                                    $image_url = $target_file;
                                }
                            }
                            $stmt = $conn->prepare("INSERT INTO menu_items (name, price, category_id, image_url) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("sdis", $name, $price, $category_id, $image_url);
                            if ($stmt->execute()) {
                                $menu_msg = "Menu item added!";
                            } else {
                                $menu_msg = "Error adding menu item.";
                            }
                            $stmt->close();
                        }

                        // Handle edit menu item
                        if (isset($_POST['edit_menu_id']) && isset($_POST['save_edit_menu'])) {
                            $id = intval($_POST['edit_menu_id']);
                            $name = trim($_POST['edit_menu_name']);
                            $price = floatval($_POST['edit_menu_price']);
                            $category_id = intval($_POST['edit_menu_category_id']);
                            $image_sql = '';
                            $params = [];
                            $types = '';
                            if (isset($_FILES['edit_menu_image']) && $_FILES['edit_menu_image']['tmp_name']) {
                                $target_dir = "uploads/";
                                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                                $filename = uniqid() . "_" . basename($_FILES["edit_menu_image"]["name"]);
                                $target_file = $target_dir . $filename;
                                if (move_uploaded_file($_FILES["edit_menu_image"]["tmp_name"], $target_file)) {
                                    $image_sql = ", image_url=?";
                                    $params[] = $target_file;
                                    $types .= 's';
                                }
                            }
                            $sql = "UPDATE menu_items SET name=?, price=?, category_id=?$image_sql WHERE id=?";
                            $params = array_merge([$name, $price, $category_id], $params, [$id]);
                            $types = 'sdi' . $types . 'i';
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param($types, ...$params);
                            if ($stmt->execute()) {
                                $menu_msg = "Menu item updated!";
                            } else {
                                $menu_msg = "Error updating menu item.";
                            }
                            $stmt->close();
                        }

                        // Handle delete menu item
                        if (isset($_POST['delete_menu_id'])) {
                            $id = intval($_POST['delete_menu_id']);
                            $conn->query("DELETE FROM menu_items WHERE id=$id");
                            $menu_msg = "Menu item deleted.";
                        }

                        // Fetch menu items with category name
                        $menu_items = [];
                        $result = $conn->query("SELECT m.*, c.name AS category_name FROM menu_items m LEFT JOIN categories c ON m.category_id = c.id ORDER BY m.id DESC");
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $menu_items[] = $row;
                            }
                        }
                    }
                } catch (Exception $e) {
                    $db_error = true;
                }
                ?>
                <?php if ($db_error): ?>
                    <div style="color:#c62828;background:#fff3e0;padding:18px;border-radius:8px;margin-bottom:18px;text-align:center;">
                        <b>Database not setup.</b> Please run the <b>Setup Database</b> tab to initialize all tables.
                    </div>
                <?php else: ?>
                    <?php if ($menu_msg): ?>
                        <div style="color:green;margin-bottom:10px;"><?php echo htmlspecialchars($menu_msg); ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" style="margin-bottom:24px;">
                        <h3>Add Menu Item</h3>
                        <input type="text" name="menu_name" placeholder="Name" required style="padding:8px;margin-right:8px;">
                        <input type="number" step="0.01" name="menu_price" placeholder="Price" required style="padding:8px;margin-right:8px;width:100px;">
                        <select name="menu_category_id" required style="padding:8px;margin-right:8px;width:140px;">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="file" name="menu_image" accept="image/*" style="margin-right:8px;">
                        <button type="submit" name="add_menu" class="btn">Add</button>
                    </form>
                    <h3>Current Menu Items</h3>
                    <table style="width:100%;border-collapse:collapse;">
                        <tr style="background:#e9f5e1;">
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($menu_items as $item): ?>
                            <tr>
                                <td><?php echo $item['id']; ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>&#8377;<?php echo htmlspecialchars($item['price']); ?></td>
                                <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                <td>
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="img" style="width:50px;height:40px;object-fit:cover;">
                                    <?php endif; ?>
                                </td>
                                <td style="display:flex;gap:8px;">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_menu_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn" style="background:#c62828;padding:7px 14px;font-size:0.98em;">Delete</button>
                                    </form>
                                    <button type="button" class="btn" style="background:#e0b96a;color:#222;padding:7px 14px;font-size:0.98em;" onclick="showEditForm(<?php echo $item['id']; ?>)">Edit</button>
                                </td>
                            </tr>
                            <tr id="edit-row-<?php echo $item['id']; ?>" style="display:none;">
                                <td colspan="6">
                                    <form method="POST" enctype="multipart/form-data" style="background:#f5ecd7;padding:16px 12px;border-radius:8px;display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
                                        <input type="hidden" name="edit_menu_id" value="<?php echo $item['id']; ?>">
                                        <input type="text" name="edit_menu_name" value="<?php echo htmlspecialchars($item['name']); ?>" required placeholder="Name" style="padding:8px;width:120px;">
                                        <input type="number" step="0.01" name="edit_menu_price" value="<?php echo htmlspecialchars($item['price']); ?>" required placeholder="Price" style="padding:8px;width:90px;">
                                        <select name="edit_menu_category_id" required style="padding:8px;width:120px;">
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" <?php if ($item['category_id'] == $cat['id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="file" name="edit_menu_image" accept="image/*">
                                        <button type="submit" name="save_edit_menu" class="btn" style="background:#388e3c;">Save</button>
                                        <button type="button" class="btn" style="background:#c62828;" onclick="hideEditForm(<?php echo $item['id']; ?>)">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <script>
                    function showEditForm(id) {
                        document.getElementById('edit-row-' + id).style.display = '';
                    }
                    function hideEditForm(id) {
                        document.getElementById('edit-row-' + id).style.display = 'none';
                    }
                    </script>
                    <?php if (isset($conn)) $conn->close(); ?>
                <?php endif; ?>
            </div>
        </div>
        <div id="tab-tables" class="admin-tab-content" style="display:none;">
            <h2>Table Management</h2>
            <div id="table-management-content">
                <?php
                // Table CRUD logic
                $table_msg = '';
                // table stuff
                $host = 'localhost';
                $port = 3307;
                // $table_msg = "table test";
                $user = 'root';
                $password = '';
                $database = 'restaurant_menu';
                $conn = new mysqli($host, $user, $password, $database, $port);

                // Handle add table
                if (isset($_POST['add_table'])) {
                    $table_number = intval($_POST['table_number']);
                    $stmt = $conn->prepare("INSERT INTO tables (table_number) VALUES (?)");
                    $stmt->bind_param("i", $table_number);
                    if ($stmt->execute()) {
                        $table_msg = "Table added!";
                    } else {
                        $table_msg = "Error adding table.";
                    }
                    $stmt->close();
                }

                // Handle delete table
                if (isset($_POST['delete_table_id'])) {
                    $id = intval($_POST['delete_table_id']);
                    $conn->query("DELETE FROM tables WHERE id=$id");
                    $table_msg = "Table deleted.";
                }

                // Fetch tables
                $tables = [];
                $result = $conn->query("SELECT * FROM tables ORDER BY table_number ASC");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $tables[] = $row;
                    }
                }
                ?>
                <?php if ($table_msg): ?>
                    <div style="color:green;margin-bottom:10px;"><?php echo htmlspecialchars($table_msg); ?></div>
                <?php endif; ?>
                <form method="POST" style="margin-bottom:24px;">
                    <h3>Add Table</h3>
                    <input type="number" name="table_number" placeholder="Table Number" required style="padding:8px;margin-right:8px;width:120px;">
                    <button type="submit" name="add_table" class="btn">Add</button>
                </form>
                <h3>Current Tables</h3>
                <table style="width:100%;border-collapse:collapse;">
                    <tr style="background:#e9f5e1;">
                        <th>ID</th>
                        <th>Table Number</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($tables as $table): ?>
                        <tr>
                            <td><?php echo $table['id']; ?></td>
                            <td><?php echo htmlspecialchars($table['table_number']); ?></td>
                            <td id="table-status-<?php echo $table['id']; ?>"><?php echo htmlspecialchars($table['status']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="delete_table_id" value="<?php echo $table['id']; ?>">
                                    <button type="submit" class="btn" style="background:#c62828;">Delete</button>
                                </form>
                                <?php if ($table['status'] === 'Available'): ?>
                                    <button type="button" class="btn" style="background:#388e3c;" onclick="markCompleted(<?php echo $table['id']; ?>)">Mark Completed</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php $conn->close(); ?>
                <script>
                function markCompleted(tableId) {
                    const statusCell = document.getElementById('table-status-' + tableId);
                    statusCell.textContent = 'Completed';
                    fetch('admin.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `mark_completed=1&table_id=${tableId}`
                    }).then(() => {
                        setTimeout(() => {
                            statusCell.textContent = 'Available';
                            fetch('admin.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `revert_status=1&table_id=${tableId}`
                            });
                        }, 3000);
                    });
                }
                </script>
            </div>
        </div>
        <div id="tab-setup" class="admin-tab-content" style="display:none;">
            <h2>Database Setup/Reset</h2>
<form method="post">
    <button type="submit" name="run_setup" class="btn setup-btn">Run Setup Database</button>
</form>
<?php
if (isset($_POST['run_setup'])) {
    include 'setup.php';
    echo "<div style='color:green;margin-top:10px;'>Database setup completed successfully.</div>";
}
if (isset($_POST['mark_completed']) && isset($_POST['table_id'])) {
    $table_id = intval($_POST['table_id']);
    $conn = new mysqli($host, $user, $password, $database, $port);
    $conn->query("UPDATE tables SET status='Completed' WHERE id=$table_id");
    $conn->close();
    exit;
}

if (isset($_POST['revert_status']) && isset($_POST['table_id'])) {
    $table_id = intval($_POST['table_id']);
    $conn = new mysqli($host, $user, $password, $database, $port);
    $conn->query("UPDATE tables SET status='Available' WHERE id=$table_id");
    $conn->close();
    exit;
}
?>
            <p style="margin-top:10px;">This will reset all tables and data.</p>
        </div>
        <script>
        function showTab(tab) {
            // this is for switching tabs, please work 
            document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.admin-tab-content').forEach(c => c.style.display = 'none');
            document.querySelector('.admin-tab[onclick*="' + tab + '"]').classList.add('active');
            document.getElementById('tab-' + tab).style.display = '';
            // let x = 1; // test var
        }
        </script>
    <?php endif; ?>
</div>
</body>
</html>

</file_content>

</diff>
</replace_in_file>
