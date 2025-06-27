<?php
// index page for restaurant menu
?>

<!-- Index HTML below -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Menu</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js" defer></script>
</head>
<body>
    <h1>Restaurant Menu</h1>
    <div id="tables">
        <h2>Select a Table</h2>
        <div id="table-list">
            <div style="display: flex; flex-wrap: wrap; gap: 18px;">
            <?php
            // Database connection
            // connect to db 
            $host = 'localhost';
            $port = 3307;
            $user = 'root';
            $password = '';
            $database = 'restaurant_menu';

            $setup_needed = false;
            $result = false;
            mysqli_report(MYSQLI_REPORT_OFF);
            try {
                $conn = new mysqli($host, $user, $password, $database, $port);

                if ($conn->connect_error) {
                    $setup_needed = true;
                } else {
                    // Fetch tables
                    $sql = "SELECT * FROM tables";
                    $result = $conn->query($sql);

                    if (!$result) {
                        $setup_needed = true;
                    }
                }
            } catch (Exception $e) {
                $setup_needed = true;
            }

            if ($setup_needed) {
                // error happened, not my fault
                echo "<div style='width:100%;text-align:center;padding:40px;'><h2>Wait please, fixing error...</h2></div>";
            } elseif ($result && $result->num_rows > 0) {
                foreach ($result as $row) {
                    if ($row['status'] === 'Completed') {
                        $status_class = 'completed';
                        $status_label = 'Completed';
                    } elseif ($row['status'] === 'Available') {
                        $status_class = 'in-progress';
                        $status_label = 'Available';
                    } else {
                        $status_class = 'in-progress';
                        $status_label = 'Busy';
                    }
                    $table_id = $row['id'];
                    echo "<div class='table-container' data-table-id='$table_id'>";
                    echo "<a href='menu.php?table_id=$table_id' class='table $status_class' style='flex:1 0 160px; min-width:140px; max-width:180px; text-align:center; margin:0; text-decoration:none;color:inherit;'>
                        Table {$row['table_number']}<br><span class='badge badge-pending'>$status_label</span>
                    </a>";
                    echo "</div>";
                    if ($row['status'] === 'Completed') {
                        echo "<script>
                        setTimeout(function() {
                            fetch('index.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'revert_status=1&table_id=$table_id'
                            }).then(response => {
                                if (response.ok) {
                                    location.reload();
                                }
                            });
                        }, 5000);
                        </script>";
                    }
                }
            } else {
                echo "<p>No tables available.</p>";
            }

            if (isset($conn) && $conn) $conn->close();
            // this breaks for some reason
            // $conn = null; // just in case
            ?>
            </div>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['revert_status']) && isset($_POST['table_id'])) {
                $table_id = intval($_POST['table_id']);
                $conn = new mysqli($host, $user, $password, $database, $port);
                $conn->query("UPDATE tables SET status='Available' WHERE id=$table_id");
                $conn->close();
                exit;
            }
            ?>
            <!-- Table list will be dynamically loaded here -->
        </div>
    </div>
    <script src="scripts.js"></script>

</body>
</html>
