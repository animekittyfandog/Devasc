<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkSense - Admin Parking</title>
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
                <a href="#" class="active">Admin</a>
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
                <h2>Admin Parking</h2>

                <div class="notification-bell" id="notification-container">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="nav-badge">0</span>

                    <div class="notification-popup" id="notification-popup">
                        <div class="popup-list" id="popup-list">
                            </div>
                    </div>
                </div>
            </header>

            <div class="camera-feeds">
                <div class="feed-container">
                    <button class="feed-placeholder" type="button">Camera Feed 1</button>
                    
                </div>
                <div class="feed-container">
                    <button class="feed-placeholder" type="button">Camera Feed 2</button>
                    
                </div>
            </div>
        </main>
        
    </div>

    <script src="parksense.js"></script>

</body>
</html>