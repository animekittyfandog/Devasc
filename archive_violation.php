<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. INCLUDE PHP MAILER
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include 'db_connect.php';
header('Content-Type: application/json');

// --- AUTO-DELETE OLD RECORDS (30 DAYS POLICY) ---
$cleanup_sql = "DELETE FROM archive WHERE archive_time < (NOW() - INTERVAL 30 DAY)";
$conn->query($cleanup_sql);

if (!isset($_POST['violation_id']) || !is_numeric($_POST['violation_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Violation ID.']);
    exit;
}

$violation_id = intval($_POST['violation_id']);
$conn->begin_transaction();

try {
    // 1. GET VIOLATION DETAILS
    $stmt_select = $conn->prepare("SELECT * FROM violations WHERE id = ?");
    $stmt_select->bind_param("i", $violation_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    if ($result->num_rows === 0) { throw new Exception("Violation not found."); }
    $violation = $result->fetch_assoc();
    $stmt_select->close();

    // 2. GET OWNER DETAILS
    $stmt_owner = $conn->prepare("SELECT email FROM registered_vehicles WHERE license_plate = ?");
    $stmt_owner->bind_param("s", $violation['license_plate']);
    $stmt_owner->execute();
    $owner_result = $stmt_owner->get_result();
    $owner = $owner_result->fetch_assoc(); 
    $stmt_owner->close();

    // 3. GET VIOLATION COUNT
    $stmt_count = $conn->prepare("SELECT COUNT(*) FROM archive WHERE license_plate = ?");
    $stmt_count->bind_param("s", $violation['license_plate']);
    $stmt_count->execute();
    $count_result = $stmt_count->get_result();
    $past_count = $count_result->fetch_row()[0];
    $current_count = $past_count + 1; 
    $stmt_count->close();
    
    // Determine Suffix (1st, 2nd, 3rd)
    $suffix = "th";
    if ($current_count % 10 == 1 && $current_count % 100 != 11) $suffix = "st";
    elseif ($current_count % 10 == 2 && $current_count % 100 != 12) $suffix = "nd";
    elseif ($current_count % 10 == 3 && $current_count % 100 != 13) $suffix = "rd";
    $violation_count_str = $current_count . $suffix . " offense";

    // 4. PREPARE EMAIL CONTENT
    $formatted_date = date('F d, Y', strtotime($violation['violation_time']));
    $formatted_time = date('g:i A', strtotime($violation['violation_time']));
    $fine_amount = "1,000.00"; 
    $location_text = "UST Parking Grounds"; // Or derive from violation_description if preferred

    // WARNING: Only appears if this is the 3rd strike
    $revocation_warning = "";
    if ($current_count >= 3) {
        $revocation_warning = "
        <div style='background-color: #ffcccc; border: 1px solid #ff0000; padding: 15px; margin-bottom: 20px; color: #a00000;'>
            <strong>URGENT NOTICE:</strong> This is your 3rd offense. Your vehicle registration has been <strong>REVOKED</strong> and your records are being removed from the ParkSense system.
        </div>";
    }

    // 5. SEND EMAIL
    if ($owner && !empty($owner['email'])) {
        $mail = new PHPMailer(true);
        try {
            // --- SERVER SETTINGS ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';     
            $mail->SMTPAuth   = true;                 
            $mail->Username   = 'renieboy.absalon.cics@ust.edu.ph'; // UPDATE THIS
            $mail->Password   = 'oqrk ixqk gfvn cydl';    // UPDATE THIS
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // --- RECIPIENTS ---
            $mail->setFrom('no-reply@parksense.ust.edu.ph', 'ParkSense Admin');
            $mail->addAddress($owner['email']); 

            $mail->isHTML(true);
            $mail->Subject = 'Official Notice of Parking Violation - ParkSense - Violation';

            // --- HTML BODY MATCHING PDF ---
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #000; line-height: 1.6; max-width: 800px;'>
                
                <p>Dear Vehicle Owner,</p>
                <p>This email serves as an official notice regarding a parking violation recorded by the ParkSense system.</p>

                $revocation_warning

                <h3 style='border-bottom: 2px solid #333; padding-bottom: 5px;'>Violation Details</h3>
                
                <table style='width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 20px;'>
                    <tr style='background-color: #f9f9f9;'>
                        <td style='border: 1px solid #000; padding: 10px; font-weight: bold; width: 35%;'>Field</td>
                        <td style='border: 1px solid #000; padding: 10px; font-weight: bold;'>Detail</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #000; padding: 10px;'><strong>Violation Date/Time</strong></td>
                        <td style='border: 1px solid #000; padding: 10px;'>$formatted_date at $formatted_time</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #000; padding: 10px;'><strong>Location</strong></td>
                        <td style='border: 1px solid #000; padding: 10px;'>$location_text</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #000; padding: 10px;'><strong>License Plate</strong></td>
                        <td style='border: 1px solid #000; padding: 10px;'>{$violation['license_plate']}</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #000; padding: 10px;'><strong>Violation Type</strong></td>
                        <td style='border: 1px solid #000; padding: 10px;'>{$violation['violation_description']}</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #000; padding: 10px;'><strong>Fine Amount</strong></td>
                        <td style='border: 1px solid #000; padding: 10px;'>PHP $fine_amount</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #000; padding: 10px;'><strong>Violation Count</strong></td>
                        <td style='border: 1px solid #000; padding: 10px;'>$violation_count_str</td>
                    </tr>
                </table>

                <p><strong>Important Note regarding Violation Count:</strong><br>
                Your current violation count is <strong>$current_count</strong>. Please be aware that repeat violations may lead to escalated fines or vehicle towing, as outlined in the Parking Policy.</p>

                <h3 style='margin-top: 30px;'>How to Pay Your Violation</h3>
                <p>You have <strong>5</strong> days from the date of this notice to pay the fine without penalty. Failure to pay by this deadline will result in an additional late fee.</p>
                
                <p><strong>Please proceed to the OCSS Office to settle your violation fee.</strong></p>

                <h3 style='margin-top: 30px;'>Appeals and Inquiries</h3>
                <p>If you believe this notice was issued in error, you may file an appeal within <strong>3</strong> days of this notice.</p>
                <ul>
                    <li style='margin-bottom: 10px;'><strong>To file an appeal:</strong> Visit the ParkSense Appeals Portal.</li>
                    <li><strong>For general questions:</strong> Please reply to this email or call us at (02) 8-123-4567 during business hours.</li>
                </ul>

                <p style='margin-top: 30px;'>Thank you for your cooperation.</p>
                <p>Sincerely,<br><strong style='font-size: 1.2em;'>OCSS</strong></p>
            </div>";

            $mail->send();
        } catch (Exception $e) { }
    }


    // 6. LOGIC: 3rd STRIKE = PURGE, ELSE ARCHIVE
    if ($current_count >= 3) {
        // --- CASE A: 3RD VIOLATION (PURGE USER) ---
        
        $stmt_del_curr = $conn->prepare("DELETE FROM violations WHERE id = ?");
        $stmt_del_curr->bind_param("i", $violation_id);
        $stmt_del_curr->execute();
        $stmt_del_curr->close();

        $stmt_del_reg = $conn->prepare("DELETE FROM registered_vehicles WHERE license_plate = ?");
        $stmt_del_reg->bind_param("s", $violation['license_plate']);
        $stmt_del_reg->execute();
        $stmt_del_reg->close();

        $stmt_del_arch = $conn->prepare("DELETE FROM archive WHERE license_plate = ?");
        $stmt_del_arch->bind_param("s", $violation['license_plate']);
        $stmt_del_arch->execute();
        $stmt_del_arch->close();

    } else {
        // --- CASE B: NORMAL ARCHIVE ---

        $stmt_insert = $conn->prepare("INSERT INTO archive (original_violation_id, violation_time, license_plate, violation_description, vehicle_status) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("issss", $violation['id'], $violation['violation_time'], $violation['license_plate'], $violation['violation_description'], $violation['vehicle_status']);
        $stmt_insert->execute();
        $stmt_insert->close();

        $stmt_delete = $conn->prepare("DELETE FROM violations WHERE id = ?");
        $stmt_delete->bind_param("i", $violation_id);
        $stmt_delete->execute();
        $stmt_delete->close();
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>