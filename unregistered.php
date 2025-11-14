<?php 
include 'db_connect.php';

// --- PAGINATION LOGIC ---
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Count total records for unregistered violations
$total_records_result = $conn->query("SELECT COUNT(*) FROM violations WHERE vehicle_status = 'unregistered'");
$total_records = $total_records_result->fetch_row()[0];
$total_pages = ceil($total_records / $records_per_page);

// Function to generate pagination links
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
    <!-- Font and CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .resolved-btn { background-color: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: 500; transition: background-color 0.2s ease; }
        .resolved-btn:hover { background-color: #218838; }
        .violation-table { width: 100%; border-collapse: collapse; table-layout: fixed; word-wrap: break-word; }
        .violation-table th, .violation-table td { padding: 10px; text-align: left; vertical-align: middle; }
        .violation-table th:nth-child(1) { width: 20%; } 
        .violation-table th:nth-child(2) { width: 25%; } 
        .violation-table th:nth-child(3) { width: 35%; } 
        .violation-table th:nth-child(4) { width: 20%; }
        .violation-table th { background-color: #333; color: white; }
        .violation-table tr:nth-child(odd) { background-color: #ffffff; }
        .violation-table tr:nth-child(even) { background-color: #dcdcdc; }

        .download-btn { display: inline-block; text-decoration: none; position: fixed; bottom: 20px; right: 30px; background-color: #007bff; color: white; border: none; padding: 12px 18px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; box-shadow: 0px 3px 6px rgba(0,0,0,0.2); transition: background-color 0.3s ease; }
        .download-btn:hover { background-color: #0056b3; }

        .modal-overlay { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 25px; border: 1px solid #888; width: 80%; max-width: 400px; border-radius: 10px; text-align: center; }
        .modal-content h3 { margin-top: 0; }
        .modal-buttons button { border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 0 10px; }
        #confirm-resolve-btn { background-color: #28a745; color: white; }
        #cancel-resolve-btn { background-color: #ccc; color: #333; }
        
        .pagination { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 30px; }
        .pagination a, .pagination span.disabled { display: flex; align-items: center; gap: 5px; justify-content: center; text-decoration: none; color: #333; padding: 10px 15px; border: 1px solid #ddd; border-radius: 5px; }
        .pagination a:hover { background-color: #f5f5f5; }
        .pagination a.active { background-color: #333; color: white; border-color: #333; cursor: default; }
        .pagination span.disabled { color: #aaa; background-color: #f9f9f9; cursor: not-allowed; }

        .violation-content { display: flex; flex-direction: column; min-height: 80vh; }
        .table-section { flex-grow: 1; }
        .pagination { flex-shrink: 0; padding-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div>
            <div class="sidebar-header"><div class="logo-title-container"><img src="assets/ustlogo.png" alt="UST Logo" class="header-logo"><h1>ParkSense</h1></div></div>
            <div id="current-date-time"><p id="date"></p><p id="time"></p></div>
            <div class="system-status"><h2>System Activated</h2><label class="toggle-switch"><input type="checkbox" checked><span class="slider"></span></label></div>
            <nav class="sidebar-nav"><h2>Parking Areas:</h2><a href="admin.php">Admin</a><a href="student.php">Student</a></nav>
            <nav class="sidebar-nav"><h2>Violations:</h2><a href="#" class="active">Unregistered Vehicles</a><a href="violation.php">Violation history</a><a href="archive.php">Archives</a></nav>
        </div>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Unregistered Vehicles</h2>
            <div class="notification-bell" id="notification-container"><i class="fas fa-bell"></i><span class="notification-badge">1</span><div class="notification-popup" id="notification-popup"><div class="popup-content"><p>Violation detected by</p><p><em>*license plate number*</em></p></div></div></div>
        </header>

        <div class="violation-content">
            <div class="table-section" id="tableSection">
                <table class="violation-table dark-header" id="violationTable">
                    <thead><tr><th>Time</th><th>License Plate</th><th>Violation</th><th>Actions</th></tr></thead>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationContainer = document.getElementById('notification-container');
    const notificationPopup = document.getElementById('notification-popup');
    notificationContainer.addEventListener('click', function(event) { event.stopPropagation(); notificationPopup.classList.toggle('show'); });
    window.addEventListener('click', function() { if (notificationPopup.classList.contains('show')) { notificationPopup.classList.remove('show'); } });
    
    function updateDateTime() {
        const now = new Date();
        const options = { month: 'long', day: 'numeric', year: 'numeric' };
        document.getElementById('date').textContent = now.toLocaleDateString('en-US', options);
        let hours = now.getHours(), minutes = now.getMinutes(), seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        document.getElementById('time').textContent = `${hours}:${minutes.toString().padStart(2,'0')}:${seconds.toString().padStart(2,'0')}${ampm}`;
    }
    updateDateTime(); setInterval(updateDateTime, 1000);

    async function archiveViolation(violationId) {
        const formData = new FormData();
        formData.append('violation_id', violationId);
        try {
            const response = await fetch('archive_violation.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (!result.success) {
                alert('Server Error: ' + result.message);
                return false;
            }
            return true;
        } catch (error) {
            console.error('Network error:', error);
            alert('A network error occurred. Could not resolve violation.');
            return false;
        }
    }

    const resolveModal = document.getElementById('resolve-modal');
    const confirmResolveBtn = document.getElementById('confirm-resolve-btn');
    const cancelResolveBtn = document.getElementById('cancel-resolve-btn');
    let rowToResolve = null;

    document.querySelectorAll('.resolved-btn').forEach(button => {
        button.addEventListener('click', function() {
            rowToResolve = this.closest('tr');
            resolveModal.style.display = 'block';
        });
    });

    confirmResolveBtn.addEventListener('click', async function() {
        if (rowToResolve) {
            const violationId = rowToResolve.dataset.id;
            const success = await archiveViolation(violationId);
            if (success) {
                window.location.reload(); 
            }
        }
        resolveModal.style.display = 'none';
        rowToResolve = null;
    });

    cancelResolveBtn.addEventListener('click', function() {
        resolveModal.style.display = 'none';
        rowToResolve = null;
    });
    
    window.addEventListener('click', function(event) {
        if (event.target == resolveModal) {
            resolveModal.style.display = 'none';
            rowToResolve = null;
        }
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>