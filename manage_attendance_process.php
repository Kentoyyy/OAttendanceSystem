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

    // Fetch user name and user_type
    $userQuery = $conn->prepare("SELECT name, user_type FROM users WHERE id = ?");
    $userQuery->bind_param("i", $user);
    $userQuery->execute();
    $userResult = $userQuery->get_result();
    if ($userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $userName = $userRow['name'];
        $userType = $userRow['user_type']; // 'kid' or 'adult'
    } else {
        echo 'User not found.';
        exit;
    }

    // Check if there is already an attendance record for the user today
    $checkQuery = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND date = ?");
    $checkQuery->bind_param("is", $user, $date);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows > 0) {
        // Update existing record
        $updateQuery = $conn->prepare("UPDATE attendance SET status = ?, user_type = ? WHERE user_id = ? AND date = ?");
        $updateQuery->bind_param("ssis", $status, $userType, $user, $date);
        if ($updateQuery->execute()) {
            // Log the activity
            $activity = "Updated attendance for $userName ($userType) with status $status";
            $logQuery = $conn->prepare("INSERT INTO activity_log (action, timestamp) VALUES (?, NOW())");
            $logQuery->bind_param("s", $activity);
            $logQuery->execute();
            
            echo 'Attendance updated successfully!';
        } else {
            echo 'Error: ' . $updateQuery->error;
        }
        $updateQuery->close();
    } else {
        // Insert new record
        $insertQuery = $conn->prepare("INSERT INTO attendance (user_id, status, date, user_type) VALUES (?, ?, ?, ?)");
        $insertQuery->bind_param("isss", $user, $status, $date, $userType);
        if ($insertQuery->execute()) {
            // Log the activity
            $activity = "Added new attendance record for $userName ($userType) with status $status";
            $logQuery = $conn->prepare("INSERT INTO activity_log (action, timestamp) VALUES (?, NOW())");
            $logQuery->bind_param("s", $activity);
            $logQuery->execute();
            
            echo 'Attendance added successfully!';
        } else {
            echo 'Error: ' . $insertQuery->error;
        }
        $insertQuery->close();
    }

    // Close the connection
    $conn->close();

    // Redirect back to the manage attendance page
    header("Location: manage_attendance.php?user_type=" . $userType);
    exit();
}
?>
