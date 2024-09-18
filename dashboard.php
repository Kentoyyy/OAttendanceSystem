<?php
include 'db_connection.php';
session_start();

// Query to count total users
$result = $conn->query("SELECT COUNT(*) AS total_users FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    $total_users = $row['total_users'];
} else {
    $total_users = 0; // Default value in case of error
}

// Query to count total attendance entries
$attendanceResult = $conn->query("SELECT COUNT(*) AS total_attendance FROM attendance");
if ($attendanceResult) {
    $attendanceRow = $attendanceResult->fetch_assoc();
    $total_attendance = $attendanceRow['total_attendance'];
} else {
    $total_attendance = 0; // Default value in case of error
}

// Query to count attendance entries for kids and adults using `user_type`
$attendanceKidsResult = $conn->query("SELECT 
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_kids,
    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_kids
    FROM attendance 
    JOIN users ON attendance.user_id = users.id 
    WHERE users.user_type = 'kid'
");

$attendanceAdultsResult = $conn->query("SELECT 
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_adults,
    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_adults
    FROM attendance 
    JOIN users ON attendance.user_id = users.id 
    WHERE users.user_type = 'adult'
");

if ($attendanceKidsResult && $attendanceAdultsResult) {
    $kidsRow = $attendanceKidsResult->fetch_assoc();
    $adultsRow = $attendanceAdultsResult->fetch_assoc();
    $present_kids = $kidsRow['present_kids'];
    $absent_kids = $kidsRow['absent_kids'];
    $present_adults = $adultsRow['present_adults'];
    $absent_adults = $adultsRow['absent_adults'];
} else {
    $present_kids = $absent_kids = $present_adults = $absent_adults = 0;
}

// Query to group attendance by date and count total entries per day
$attendanceSummaryResult = $conn->query("SELECT date AS attendance_date, COUNT(*) AS entries FROM attendance GROUP BY date ORDER BY attendance_date DESC");
$attendanceSummary = $attendanceSummaryResult ? $attendanceSummaryResult->fetch_all(MYSQLI_ASSOC) : [];

// Query to get recent activities
$recentActivitiesResult = $conn->query("SELECT action, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT 5");
$recentActivities = $recentActivitiesResult ? $recentActivitiesResult->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="images/church.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GGC | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
        }
        .sidebar {
            height: 100vh;
            width: 240px;
            background-color: #16325B;
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
            background-color: #55679C;
            color: #fff;
        }
        .sidebar a.active {
            background-color: #55679C;
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
            margin-bottom: 20px;
        }
        .stats table {
            width: 100%;
            border-collapse: collapse;
        }
        .stats th, .stats td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .stats th {
            background-color: #55679C;
            color: white;
        }
        .stats tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .stats tbody tr:hover {
            background-color: #e9ecef;
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
            background-color: #55679C;
            color: white;
        }
        
        .attendance-summary h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 15px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .summary-table thead {
            background-color: #55679C;
            color: white;
        }
        .summary-table th, .summary-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .summary-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .summary-table td {
            font-size: 16px;
            color: #333;
        }
        .summary-table th {
            font-size: 16px;
            text-transform: uppercase;
        }
        .summary-table tbody tr:hover {
            background-color: #e9ecef;
        }
        .total-users-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
            text-align: center;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }

        .total-users-card h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
        }

        .total-users-card p {
            font-size: 24px;
            color: #333;
            font-weight: 600;
        }

        .total-users-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <img src="images/church.png" alt="Logo">
        <h2>GGC Church</h2>
        <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="user_management.php"><i class="fas fa-user-plus"></i> User Management</a>
        <a href="manage_attendance.php"><i class="fas fa-calendar-check"></i> Manage Attendance</a>
       
    </div>

    <div class="content">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <p>Welcome to the Attendance Management System. Here you can manage users, attendance, reports, and settings.</p>

        <!-- Statistics Section -->
        <div class="stats">
            <h2>Attendance Statistics</h2>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Kids</td>
                        <td><?php echo $present_kids; ?></td>
                        <td><?php echo $absent_kids; ?></td>
                        <td><?php echo $present_kids + $absent_kids; ?></td>
                    </tr>
                    <tr>
                        <td>Adults</td>
                        <td><?php echo $present_adults; ?></td>
                        <td><?php echo $absent_adults; ?></td>
                        <td><?php echo $present_adults + $absent_adults; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td><?php echo $present_kids + $present_adults; ?></td>
                        <td><?php echo $absent_kids + $absent_adults; ?></td>
                        <td><?php echo $present_kids + $absent_kids + $present_adults + $absent_adults; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Recent Activity Section -->
        <div class="recent-activities">
            <h2>Recent Activities</h2>
            <table>
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentActivities as $activity) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($activity['action']); ?></td>
                        <td><?php echo htmlspecialchars($activity['timestamp']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Attendance Summary Section -->
        <div class="attendance-summary">
            <h2>Attendance Summary</h2>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total Entries</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendanceSummary as $summary) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($summary['attendance_date']); ?></td>
                        <td><?php echo htmlspecialchars($summary['entries']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Total Users Section -->
        <div class="total-users-card">
        <h2>Total Users</h2>
        <p><?php echo $total_users; ?></p>
    </div>
    </div>
    <footer style="background-color: #16325B; color: white; text-align: center; padding: 10px 0; margin-left: 240px; width: calc(100% - 240px);">
    <p>&copy; <?php echo date("Y"); ?> GGC Church. All rights reserved by: lalove ♡</p>
</footer>

</body>
</html>
