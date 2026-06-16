<?php 
include 'db_connect.php';

// --- PAGINATION LOGIC ---
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Count total records
$total_records_result = $conn->query("SELECT COUNT(*) FROM violations WHERE vehicle_status = 'unregistered'");
$total_records = $total_records_result->fetch_row()[0];
$total_pages = max(1, ceil($total_records / $records_per_page));

// Clamp page to valid range — prevents empty pages when records are deleted
if ($page > $total_pages) {
    header("Location: unregistered.php?page=" . $total_pages);
    exit;
}
if ($page < 1) $page = 1;

$offset = ($page - 1) * $records_per_page;

function generate_pagination_links($current_page, $total_pages, $page_param) {
    echo '<div class="pagination">';
    if ($current_page > 1) {
        $prev_page = $current_page - 1;
        echo "<a href='?{$page_param}={$prev_page}'><i class='fas fa-chevron-left'></i> Prev</a>";
    } else {
        echo "<span class='disabled'><i class='fas fa-chevron-left'></i> Prev</span>";
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            echo "<a href='#' class='active'>{$i}</a>";
        } else {
            echo "<a href='?{$page_param}={$i}'>{$i}</a>";
        }
    }
    if ($current_page < $total_pages) {
        $next_page = $current_page + 1;
        echo "<a href='?{$page_param}={$next_page}'>Next <i class='fas fa-chevron-right'></i></a>";
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
    <title>ParkSense - Unregistered Vehicles</title>
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

    <main class="main-content fade-in-content">
        <header class="main-header">
            <h2>Unregistered Vehicles</h2>

            <div class="notification-bell" id="notification-container">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="nav-badge">0</span>

                <div class="notification-popup" id="notification-popup">
                    <div class="popup-list" id="popup-list">
                        </div>
                </div>
            </div>

        </header>

        <div class="violation-content">
            <div class="table-section" id="tableSection">
                <table class="violation-table dark-header" id="violationTable">
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
                        $sql = "SELECT * FROM violations WHERE vehicle_status = 'unregistered' ORDER BY violation_time ASC LIMIT ? OFFSET ?";                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ii", $records_per_page, $offset);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr data-id='" . htmlspecialchars($row['id']) . "'>";
                                echo "<td>" . date('M d, Y <br> g:i A', strtotime($row['violation_time'])) . "</td>";
                                echo "<td>" . htmlspecialchars($row['license_plate']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['violation_description']) . "</td>";
                                echo "<td><button class='resolved-btn'>Resolve</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No unregistered violations found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div> 
            
            <?php 
                if ($total_pages > 1) {
                    generate_pagination_links($page, $total_pages, 'page');
                }
            ?>
        </div>
    </main>
</div>

<a href="download_unregistered.php" target="_blank" class="download-btn">
    <i class="fas fa-file-download"></i> Download PDF
</a>

<!-- Resolve Confirmation Modal -->
<div id="resolve-modal" class="modal-overlay">
    <div class="modal-content">
        <h3>Resolve Violation</h3>
        <p>Are you sure you want to resolve this violation? It will be moved to the archive.</p>
        <div class="modal-buttons">
            <button id="cancel-resolve-btn">Cancel</button>
            <button id="confirm-resolve-btn">Yes, Resolve</button>
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