<?php include 'db_connect.php'; ?>
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
        .resolved-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .resolved-btn:hover {
            background-color: #218838;
        }

        .resolved-row {
            background-color: #d3d3d3 !important;
            opacity: 0.8;
        }

        .violation-table {
            width: 100%;
            border-collapse: collapse;
        }

        .violation-table th, .violation-table td {
            padding: 10px;
            text-align: left;
        }

        .violation-table th {
            background-color: #333;
            color: white;
        }
        
        .resolved-btn.checked {
            background-color: gray !important;
            cursor: not-allowed;
        }

        .resolved-btn.checked::before {
            content: "✔ ";
        }

        /* --- Added for PDF Download Button --- */
        .download-btn {
            position: fixed;
            bottom: 20px;
            right: 30px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0px 3px 6px rgba(0,0,0,0.2);
            transition: background-color 0.3s ease;
        }

        .download-btn:hover {
            background-color: #0056b3;
        }
    </style>
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
                <a href="#" class="active">Unregistered Vehicles</a>
                <a href="violation.php">Violation history</a>
            </nav>
        </div>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Unregistered Vehicles</h2>
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
            <div class="table-section" id="tableSection">
                <table class="violation-table dark-header" id="violationTable">
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
                        $sql = "SELECT * FROM violations WHERE vehicle_status = 'unregistered' ORDER BY violation_time ASC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . date('g:i A', strtotime($row['violation_time'])) . "</td>";
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
        </div>
    </main>
</div>

<!-- ✅ Added Download Button -->
<button class="download-btn" id="downloadPDF"><i class="fas fa-file-download"></i> Download PDF</button>

<!-- ✅ Added Libraries for PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationContainer = document.getElementById('notification-container');
    const notificationPopup = document.getElementById('notification-popup');
    const tableBody = document.querySelector('#violationTable tbody');

    // --- Notification toggle ---
    notificationContainer.addEventListener('click', function(event) {
        event.stopPropagation();
        notificationPopup.classList.toggle('show');
    });

    window.addEventListener('click', function() {
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

    // --- Handle "Resolve" button clicks ---
    document.querySelectorAll('.resolved-btn').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');

            if (!row.classList.contains('resolved-row')) {
                // ✅ Resolve → move to bottom
                row.classList.add('resolved-row');
                this.classList.add('checked');
                this.textContent = 'Resolved';
                tableBody.appendChild(row);
            } else {
                // 🔄 Unresolve → move to top
                row.classList.remove('resolved-row');
                this.classList.remove('checked');
                this.textContent = 'Resolve';
                tableBody.insertBefore(row, tableBody.firstChild);
            }

        });
    });

    // --- PDF Download Feature ---
    document.getElementById('downloadPDF').addEventListener('click', async () => {
        const { jsPDF } = window.jspdf;
        const tableSection = document.getElementById('tableSection');
        const canvas = await html2canvas(tableSection);
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF('p', 'mm', 'a4');

        const pdfWidth = pdf.internal.pageSize.getWidth();
        const imgHeight = (canvas.height * pdfWidth) / canvas.width;

        pdf.text("ParkSense - Unregistered Vehicles", 14, 15);
        pdf.addImage(imgData, 'PNG', 10, 25, pdfWidth - 20, imgHeight);
        pdf.save("Unregistered_Vehicles.pdf");
    });
});
</script>


</body>
</html>

<?php $conn->close(); ?>