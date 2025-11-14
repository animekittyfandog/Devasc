<?php
include 'db_connect.php';

$records_per_page = 5;

// --- PAGINATION FOR REGISTERED VEHICLES ---
$page_reg = isset($_GET['page_reg']) && is_numeric($_GET['page_reg']) ? (int)$_GET['page_reg'] : 1;
$offset_reg = ($page_reg - 1) * $records_per_page;

// Count total records for registered
$total_reg_result = $conn->query("SELECT COUNT(*) FROM archive WHERE vehicle_status = 'registered'");
$total_reg_records = $total_reg_result->fetch_row()[0];
$total_reg_pages = ceil($total_reg_records / $records_per_page);

// --- PAGINATION FOR UNREGISTERED VEHICLES ---
$page_unreg = isset($_GET['page_unreg']) && is_numeric($_GET['page_unreg']) ? (int)$_GET['page_unreg'] : 1;
$offset_unreg = ($page_unreg - 1) * $records_per_page;

// Count total records for unregistered
$total_unreg_result = $conn->query("SELECT COUNT(*) FROM archive WHERE vehicle_status = 'unregistered'");
$total_unreg_records = $total_unreg_result->fetch_row()[0];
$total_unreg_pages = ceil($total_unreg_records / $records_per_page);


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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    .violation-section h2 { text-decoration: underline; font-size: 1.8em; font-weight: bold; margin-bottom: 20px; }
    .violation-section { margin-bottom: 50px; }
    .violation-table { width: 100%; border-collapse: collapse; table-layout: fixed; word-wrap: break-word; }
    .violation-table th, .violation-table td { padding: 10px; text-align: left; vertical-align: middle; }
    .violation-table th { background-color: #333; color: white; }
    .violation-table tr:nth-child(odd) { background-color: #ffffff; }
    .violation-table tr:nth-child(even) { background-color: #dcdcdc; }
    .violation-table tr:last-child td { border-bottom: none; }
    
    .restore-btn { background-color: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; }
    .restore-btn:hover { background-color: #218838; }
    
    .pagination { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 30px; }
    .pagination a, .pagination span.disabled { display: flex; align-items: center; gap: 5px; justify-content: center; text-decoration: none; color: #333; padding: 10px 15px; border: 1px solid #ddd; border-radius: 5px; }
    .pagination a:hover { background-color: #f5f5f5; }
    .pagination a.active { background-color: #333; color: white; border-color: #333; cursor: default; }
    .pagination span.disabled { color: #aaa; background-color: #f9f9f9; cursor: not-allowed; }
    
    .download-btn { display: inline-block; text-decoration: none; position: fixed; bottom: 20px; right: 30px; background-color: #007bff; color: white; border: none; padding: 12px 18px; border-radius: 8px; cursor: pointer; font-size: 16px; box-shadow: 0px 3px 6px rgba(0,0,0,0.2); }
    .download-btn:hover { background-color: #0056b3; }

    .modal-overlay { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: #fefefe; margin: 15% auto; padding: 25px; border: 1px solid #888; width: 80%; max-width: 400px; border-radius: 10px; text-align: center; }
    .modal-content h3 { margin-top: 0; }
    .modal-buttons button { border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 0 10px; }
    #confirm-restore-btn { background-color: #28a745; color: white; }
    #cancel-restore-btn { background-color: #ccc; color: #333; }

    .violation-section { display: flex; flex-direction: column; min-height: 450px; }
    .table-wrapper { flex-grow: 1; }
    .pagination { flex-shrink: 0; padding-bottom: 20px;}
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
            <nav class="sidebar-nav"><h2>Violations:</h2><a href="unregistered.php">Unregistered Vehicles</a><a href="violation.php">Violation history</a><a href="archive.php" class="active">Archives</a> </nav>
        </div>
    </aside>
    <main class="main-content" id="archiveContent">
        <header class="main-header">
            <h2>Archives</h2>
            <div class="notification-bell" id="notification-container"><i class="fas fa-bell"></i><span class="notification-badge">1</span><div class="notification-popup" id="notification-popup"><div class="popup-content"><p>Violation detected by</p><p><em>*license plate number*</em></p></div></div></div>
        </header>

        <div class="violation-section">
            <h2>Violation History</h2>
            <div class="table-wrapper"> 
            <table class="violation-table">
                <thead><tr><th style="width: 20%;">Time</th><th style="width: 25%;">License Plate</th><th style="width: 40%;">Violation</th><th style="width: 15%;">Actions</th></tr></thead>
                <tbody>
                    <?php
                        $sql_registered = "SELECT * FROM archive WHERE vehicle_status = 'registered' ORDER BY violation_time ASC LIMIT ? OFFSET ?";                        $stmt_reg = $conn->prepare($sql_registered);
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
                    <thead><tr><th style="width: 20%;">Time</th><th style="width: 25%;">License Plate</th><th style="width: 40%;">Violation</th><th style="width: 15%;">Actions</th></tr></thead>
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

<a href="download_archive.php" target="_blank" class="download-btn">
    <i class="fas fa-file-download"></i> Download PDF
</a>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
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
    const notificationContainer = document.getElementById('notification-container');
    const notificationPopup = document.getElementById('notification-popup');
    notificationContainer.addEventListener('click', function(event) { event.stopPropagation(); notificationPopup.classList.toggle('show'); });
    window.addEventListener('click', function() { if (notificationPopup.classList.contains('show')) { notificationPopup.classList.remove('show'); } });

    const restoreModal = document.getElementById('restore-modal');
    let rowToRestore = null;

    async function restoreViolation(archiveId) {
        const formData = new FormData();
        formData.append('archive_id', archiveId);
        try {
            const response = await fetch('restore_violation.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                return true;
            } else {
                alert('Server Error: ' + result.message);
                return false;
            }
        } catch (error) {
            console.error('Network error:', error);
            alert('A network error occurred. Could not restore violation.');
            return false;
        }
    }

    document.querySelector('.main-content').addEventListener('click', function(event) {
        if (event.target.classList.contains('restore-btn')) {
            rowToRestore = event.target.closest('tr');
            restoreModal.style.display = 'block';
        }
    });

    document.getElementById('confirm-restore-btn').addEventListener('click', async function() {
        if (rowToRestore) {
            const archiveId = rowToRestore.dataset.id;
            const success = await restoreViolation(archiveId);
            if (success) {
                rowToRestore.remove();
                window.location.reload();
            }
        }
        restoreModal.style.display = 'none';
        rowToRestore = null;
    });

    document.getElementById('cancel-restore-btn').addEventListener('click', function() {
        restoreModal.style.display = 'none';
        rowToRestore = null;
    });

    window.addEventListener('click', function(event) {
        if (event.target == restoreModal) {
            restoreModal.style.display = 'none';
            rowToRestore = null;
        }
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>