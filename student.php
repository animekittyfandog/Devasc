<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkSense - Student Parking</title>
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
                <a href="#" class="active">Student</a>
            </nav> 

            <nav class="sidebar-nav">
                <h2>Violations:</h2>
                <a href="unregistered.php">Unregistered Vehicles</a>
                <a href="violation.php">Violation history</a>
            </nav>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h2>Student Parking</h2>
                <div class="notification-bell" id="notification-container">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">1</span>

                    <!-- notification popup -->
                    <div class="notification-popup" id="notification-popup">
                        <div class="popup-content">
                            <p>Violation detected by</p>
                            <p><em>*license plate number*</em></p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="camera-feeds">
                <div class="feed-container">
                    <button class="feed-placeholder" type="button">Camera Feed 1</button>
                    <p>Available parking: 1</p>
                </div>
                <div class="feed-container">
                    <button class="feed-placeholder" type="button">Camera Feed 2</button>
                    <p>Available parking: 1</p>
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
            
            // Clock
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
            
            // Initial call and set interval to update every second
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });
    </script>

</body>
</html>