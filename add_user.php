<?php
include 'db_connection.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $dob = $_POST['dob'];

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO users (name, email, contact, dob) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $contact, $dob);

    if ($stmt->execute()) {
        // Log the activity
        $action = "Added new user: $name";
        $logStmt = $conn->prepare("INSERT INTO activity_log (action) VALUES (?)");
        $logStmt->bind_param("s", $action);
        $logStmt->execute();
        $logStmt->close();

        // Redirect to the dashboard after successful insertion
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
        }
        .sidebar {
            height: 100vh;
            width: 220px;
            background-color: #0D7C66;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        .sidebar h2 {
            color: white;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 40px;
        }
        .sidebar a {
            font-size: 14px;
            padding: 12px 10px;
            text-align: left;
            text-decoration: none;
            color: white;
            width: 180px;
            border-radius: 4px;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .sidebar a:hover {
            background-color: #095b4e;
            color: #e0f0ec;
        }
        .sidebar a.active {
            background-color: #063e34;
        }
        .sidebar i {
            margin-right: 15px;
            font-size: 16px;
        }
        .content {
            margin-left: 220px;
            padding: 40px;
            background-color: #ffffff;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .content h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }
        .content form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .content form .form-group {
            margin-bottom: 20px;
        }
        .content form .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .content form .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .content form .form-group input:focus {
            border-color: #0D7C66;
            outline: none;
        }
        .content form .form-group button {
            background-color: #0D7C66;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .content form .form-group button:hover {
            background-color: #095b4e;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <!-- Logo Image -->
        <img src="path_to_your_logo_image/logo.png" alt="Logo">

        <h2>Attendance System</h2>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="add_user.php" class="active"><i class="fas fa-user-plus"></i> Add User</a>
        <a href="manage_attendance.php"><i class="fas fa-calendar-check"></i> Manage Attendance</a>
        <a href="#"><i class="fas fa-chart-line"></i> Reports</a>
        <a href="#"><i class="fas fa-cog"></i> Settings</a>
    </div>

    <div class="content">
        <h1><i class="fas fa-user-plus"></i> Add User</h1>
        <form action="add_user.php" method="POST">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="contact">Contact Number</label>
                <input type="tel" id="contact" name="contact" required>
            </div>
            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob" required>
            </div>
            <div class="form-group">
                <button type="submit">Add User</button>
            </div>
        </form>
    </div>

    <!-- Include Font Awesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

</body>
</html>
