<?php
include 'db_connect.php';

// --- AUTO-DELETE OLD RECORDS (30 DAYS POLICY) ---
$cleanup_sql = "DELETE FROM archive WHERE archive_time < (NOW() - INTERVAL 30 DAY)";
$conn->query($cleanup_sql);
// ------------------------------------------------

$records_per_page = 5;

// --- PAGINATION FOR REGISTERED VEHICLES ---
$page_reg = isset($_GET['page_reg']) && is_numeric($_GET['page_reg']) ? (int)$_GET['page_reg'] : 1;

// Count total records for registered
$total_reg_result = $conn->query("SELECT COUNT(*) FROM archive WHERE vehicle_status = 'registered'");
$total_reg_records = $total_reg_result->fetch_row()[0];
$total_reg_pages = max(1, ceil($total_reg_records / $records_per_page));

$page_unreg = isset($_GET['page_unreg']) && is_numeric($_GET['page_unreg']) ? (int)$_GET['page_unreg'] : 1;

// Count total records for unregistered
$total_unreg_result = $conn->query("SELECT COUNT(*) FROM archive WHERE vehicle_status = 'unregistered'");
$total_unreg_records = $total_unreg_result->fetch_row()[0];
$total_unreg_pages = max(1, ceil($total_unreg_records / $records_per_page));

// Clamp both pages to valid range — prevents empty pages when records are deleted
if ($page_reg > $total_reg_pages)     $page_reg   = $total_reg_pages;
if ($page_reg < 1)                    $page_reg   = 1;
if ($page_unreg > $total_unreg_pages) $page_unreg = $total_unreg_pages;
if ($page_unreg < 1)                  $page_unreg = 1;

$offset_reg   = ($page_reg   - 1) * $records_per_page;
$offset_unreg = ($page_unreg - 1) * $records_per_page;


// Function to generate the pagination links
function generate_pagination_links($current_page, $total_pages, $page_param, $other_params) {
    echo '<div class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $prev_page = $current_page - 1;
        echo "<a href='?{$page_param}={$prev_page}&{$other_params}'><i class='fas fa-chevron-left'></i> Prev</a>";
    } else {
        echo "<span class='disabled'><i class='fas fa-chevron-left'></i> Prev</span>";
    }

    // Page number links
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            echo "<a href='#' class='active'>{$i}</a>";
        } else {
            echo "<a href='?{$page_param}={$i}&{$other_params}'>{$i}</a>";
        }
    }

    // Next button
    if ($current_page < $total_pages) {
        $next_page = $current_page + 1;
        echo "<a href='?{$page_param}={$next_page}&{$other_params}'>Next <i class='fas fa-chevron-right'></i></a>";
    } else {
        echo "<span class='disabled'>Next <i class='fas fa-chevron-right'></i></span>";
    }

    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkSense - Archives</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div>
            <div class="sidebar-header">
                <div class="logo-title-container">
                    <img src="assets/ustlogo.png" alt="UST Logo" class="header-logo">
                    <h1>ParkSense</h1>
                </div>
            </div>
            <div id="current-date-time">
                <p id="date"></p>
                <p id="time"></p>
            </div>
            <div class="system-status">
                <h2>System Activated</h2>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
            </div>
            <nav class="sidebar-nav">
                <h2>Parking Areas:</h2>
                <a href="admin.php">Admin</a>
                <a href="student.php">Student</a>
            </nav>
            <nav class="sidebar-nav">
                <h2>Violations:</h2>
                <a href="violation.php">Violation history</a>
                <a href="unregistered.php">Unregistered Vehicles</a>
                <a href="archive.php">Archives</a> 
            </nav>
        </div>
    </aside>
    <main id="archiveContent" class="main-content fade-in-content">
        <header class="main-header">
            <h2>Archives</h2>

            <div class="notification-bell" id="notification-container">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="nav-badge">0</span>

                <div class="notification-popup" id="notification-popup">
                    <div class="popup-list" id="popup-list">
                        </div>
                </div>
            </div>

        </header>

        <div class="violation-section">
            <h2>Violation History</h2>
            <div class="table-wrapper"> 
            <table class="violation-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>License Plate</th>
                        <th>Violation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $sql_registered = "SELECT * FROM archive WHERE vehicle_status = 'registered' ORDER BY violation_time ASC LIMIT ? OFFSET ?";                        
                        $stmt_reg = $conn->prepare($sql_registered);
                        $stmt_reg->bind_param("ii", $records_per_page, $offset_reg);
                        $stmt_reg->execute();
                        $result_registered = $stmt_reg->get_result();

                        if ($result_registered->num_rows > 0) {
                            while($row = $result_registered->fetch_assoc()) {
                                echo "<tr data-id='" . htmlspecialchars($row["id"]) . "'>";
                                echo "<td>" . date("M d, Y <br> g:i A", strtotime($row["violation_time"])) . "</td>";
                                echo "<td>" . htmlspecialchars($row["license_plate"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["violation_description"]) . "</td>";
                                echo '<td><button class="restore-btn">Restore</button></td>';
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No archived registered violations found.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
                        </div> 
            <?php 
                if ($total_reg_pages > 1) {
                    generate_pagination_links($page_reg, $total_reg_pages, 'page_reg', "page_unreg=$page_unreg");
                }
            ?>
        </div>

        <div class="violation-section">
            <h2>Unregistered Vehicles</h2>
            <div class="table-wrapper"> 
                <table class="violation-table">
                    <thead><tr><th>Date & Time</th><th>License Plate</th><th>Violation</th><th>Actions</th></tr></thead>                    
                    <tbody>
                        <?php
                            $sql_unregistered = "SELECT * FROM archive WHERE vehicle_status = 'unregistered' ORDER BY violation_time ASC LIMIT ? OFFSET ?";
                            $stmt_unreg = $conn->prepare($sql_unregistered);
                            $stmt_unreg->bind_param("ii", $records_per_page, $offset_unreg);
                            $stmt_unreg->execute();
                            $result_unregistered = $stmt_unreg->get_result();

                            if ($result_unregistered->num_rows > 0) {
                                while($row = $result_unregistered->fetch_assoc()) {
                                    echo "<tr data-id='" . htmlspecialchars($row["id"]) . "'>";
                                    echo "<td>" . date("M d, Y <br> g:i A", strtotime($row["violation_time"])) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["license_plate"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["violation_description"]) . "</td>";
                                    echo '<td><button class="restore-btn">Restore</button></td>';
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No archived unregistered violations found.</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div> 

             <?php 
                if ($total_unreg_pages > 1) {
                    generate_pagination_links($page_unreg, $total_unreg_pages, 'page_unreg', "page_reg=$page_reg");
                }
            ?>
        </div>
    </main>
</div>

<!-- Restore Confirmation Modal -->
<div id="restore-modal" class="modal-overlay">
    <div class="modal-content">
        <h3>Restore Violation</h3>
        <p>Are you sure you want to restore this violation? It will be moved back to the active violations list.</p>
        <div class="modal-buttons">
            <button id="cancel-restore-btn">Cancel</button>
            <button id="confirm-restore-btn">Yes, Restore</button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="modal-overlay"> <div class="modal-content">
        <div style="text-align: center; margin-bottom: 15px;">
            <i class="fas fa-check-circle" style="color: #28a745; font-size: 3.5em;"></i>
        </div>
        <h3>Success!</h3>
        <p>The action has been completed successfully.</p>
        <div class="modal-buttons" style="margin-top: 20px;">
            <button id="success-dismiss-btn">Understood</button>
        </div>
    </div>
</div>

    <script src="parksense.js"></script>

</body>
</html>
<?php $conn->close(); ?>