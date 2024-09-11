<?php
// Include the database connection file
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the form fields are set
    $user = isset($_POST['user']) ? trim($_POST['user']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    // Check if all required fields are provided
    if (empty($user) || empty($status)) {
        echo 'All fields are required.';
        exit;
    }

    $date = date('Y-m-d'); // Current date

    // Check if there is already an attendance record for the user today
    $checkQuery = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND date = ?");
    $checkQuery->bind_param("is", $user, $date);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows > 0) {
        // Update existing record
        $updateQuery = $conn->prepare("UPDATE attendance SET status = ? WHERE user_id = ? AND date = ?");
        $updateQuery->bind_param("sis", $status, $user, $date);
        if ($updateQuery->execute()) {
            echo 'Attendance updated successfully!';
        } else {
            echo 'Error: ' . $updateQuery->error;
        }
        $updateQuery->close();
    } else {
        // Insert new record
        $insertQuery = $conn->prepare("INSERT INTO attendance (user_id, status, date) VALUES (?, ?, ?)");
        $insertQuery->bind_param("iss", $user, $status, $date);
        if ($insertQuery->execute()) {
            echo 'Attendance added successfully!';
        } else {
            echo 'Error: ' . $insertQuery->error;
        }
        $insertQuery->close();
    }

    // Close the connection
    $conn->close();

    // Redirect back to the manage attendance page
    header("Location: manage_attendance.php");
    exit();
}
?>
