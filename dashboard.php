<?php
include 'db_connection.php';

// Query to count total users
$result = $conn->query("SELECT COUNT(*) AS total_users FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    $total_users = $row['total_users'];
} else {
    $total_users = 0; // Default value in case of error
}

// Query to get recent activities
$recentActivitiesResult = $conn->query("SELECT action, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT 5");
$recentActivities = $recentActivitiesResult ? $recentActivitiesResult->fetch_all(MYSQLI_ASSOC) : [];

// Check for activity query parameter
$activity = isset($_GET['activity']) ? $_GET['activity'] : '';

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimalist Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .stats {
            display: flex;
            gap: 20px;
        }
        .card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex: 1;
            text-align: center;
        }
        .card h3 {
            margin: 0;
            font-size: 36px;
            color: #0D7C66;
        }
        .card p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #777;
        }
        .chart {
            margin-top: 20px;
        }
        .recent-activities {
            margin-top: 20px;
        }
        .recent-activities table {
            width: 100%;
            border-collapse: collapse;
        }
        .recent-activities th, .recent-activities td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .recent-activities th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <!-- Logo Image -->
        <img src="path_to_your_logo_image/logo.png" alt="Logo">

        <h2>Attendance System</h2>
        <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="user_management.php"><i class="fas fa-user-plus"></i> User Management</a>
        <a href="manage_attendance.php"><i class="fas fa-calendar-check"></i> Manage Attendance</a>
        <a href="#"><i class="fas fa-chart-line"></i> Reports</a>
        <a href="#"><i class="fas fa-cog"></i> Settings</a>
    </div>

    <div class="content">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <p>Welcome to the Attendance Management System. Here you can manage users, attendance, reports, and settings.</p>

        <!-- Statistics Section -->
        <div class="stats">
            <div class="card">
                <h3 id="userCount"><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>
            <div class="card">
                <h3 id="attendanceCount">N/A</h3>
                <p>Total Attendance Entries</p>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="recent-activities">
            <h2>Recent Activity</h2>
            <table>
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentActivities)): ?>
                        <tr>
                            <td colspan="2">No recent activities.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($activity['timestamp']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

  

</body>
</html>
