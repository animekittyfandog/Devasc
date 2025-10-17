<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkSense - Violation History</title>
    <!-- Your Font and CSS links -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
                    <a href="unregistered.php">Unregistered Vehicles</a>
                    <a href="#" class="active">Violation history</a>
                </nav>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h2>Violation History</h2>
                <div class="notification-bell" id="notification-container">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">1</span>
                    <div class="notification-popup" id="notification-popup">
                        <div class="popup-content">
                            <p>Violation detected by</p>
                            <p><em>*license plate number*</em></p>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="violation-content">
                <div class="table-section">
                    <table class="violation-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>License Plate</th>
                                <th>Violation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // Fetch registered violations from the database
                                $sql = "SELECT * FROM violations WHERE vehicle_status = 'registered' ORDER BY violation_time ASC";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    // Loop through each row and display it
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . date("g:i A", strtotime($row["violation_time"])) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["license_plate"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["violation_description"]) . "</td>";
                                        echo "<td></td>"; // Placeholder for actions
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No registered violations found.</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationContainer = document.getElementById('notification-container');
            const notificationPopup = document.getElementById('notification-popup');

            notificationContainer.addEventListener('click', function(event) {
                event.stopPropagation(); 
                notificationPopup.classList.toggle('show');
            });

            window.addEventListener('click', function(event) {
                if (notificationPopup.classList.contains('show')) {
                    notificationPopup.classList.remove('show');
                }
            });
            
            function updateDateTime() {
                const now = new Date();
                const options = { month: 'long', day: 'numeric', year: 'numeric' };
                document.getElementById('date').textContent = now.toLocaleDateString('en-US', options);
                let hours = now.getHours();
                let minutes = now.getMinutes();
                let seconds = now.getSeconds();
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; 
                minutes = minutes < 10 ? '0' + minutes : minutes;
                seconds = seconds < 10 ? '0' + seconds : seconds;
                document.getElementById('time').textContent = `${hours}:${minutes}:${seconds}${ampm}`;
            }
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });
    </script>
</body>w
</html>
<?php $conn->close(); ?>