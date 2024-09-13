<?php
include 'db_connection.php';
require 'vendor/autoload.php'; // Include Composer's autoloader

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Fetch users for attendance management
$usersResult = $conn->query("SELECT id, name FROM users");
$users = $usersResult ? $usersResult->fetch_all(MYSQLI_ASSOC) : [];

// Fetch current attendance records
$date = date('Y-m-d'); // Current date
$attendanceResult = $conn->query("SELECT users.name, attendance.status, attendance.date, attendance.time
                                  FROM attendance 
                                  JOIN users ON attendance.user_id = users.id 
                                  WHERE attendance.date = '$date'");
$attendanceRecords = $attendanceResult ? $attendanceResult->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();

// Handle Excel download
if (isset($_GET['download']) && $_GET['download'] === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set header
    $sheet->setCellValue('A1', 'User Name');
    $sheet->setCellValue('B1', 'Status');
    $sheet->setCellValue('C1', 'Date');
    $sheet->setCellValue('D1', 'Time');

    // Add data
    $row = 2;
    foreach ($attendanceRecords as $record) {
        $sheet->setCellValue('A' . $row, $record['name']);
        $sheet->setCellValue('B' . $row, $record['status']);
        $sheet->setCellValue('C' . $row, $record['date']);
        $sheet->setCellValue('D' . $row, date('h:i A', strtotime($record['time'])));
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'attendance_' . date('Ymd') . '.xlsx';

    // Send file to browser
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance</title>
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
        .content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .content table, .content th, .content td {
            border: 1px solid #ccc;
        }
        .content th, .content td {
            padding: 12px;
            text-align: left;
        }
        .content th {
            background-color: #16325B;
            color: #fff;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group button {
            padding: 10px 15px;
            background-color: #16325B;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #55679C;
        }
        .download-button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #16325B;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .download-button:hover {
            background-color: #55679C;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <img src="path_to_your_logo_image/logo.png" alt="Logo">
    <h2>Attendance System</h2>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="user_management.php"><i class="fas fa-user-plus"></i> User Management</a>
    <a href="manage_attendance.php" class="active"><i class="fas fa-calendar-check"></i> Manage Attendance</a>
    <a href="#"><i class="fas fa-chart-line"></i> Reports</a>
    <a href="#"><i class="fas fa-cog"></i> Settings</a>

    <a href="#"><i class="fas fa-logout"></i> Log out</a>
</div>

<div class="content">
    <h1><i class="fas fa-calendar-check"></i> Manage Attendance</h1>
    <form action="manage_attendance_process.php" method="POST">
        <div class="form-group">
            <label for="user">Select User</label>
            <select id="user" name="user" required>
                <option value="">--Select User--</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="status">Attendance Status</label>
            <select id="status" name="status" required>
                <option value="">--Select Status--</option>
                <option value="present">Present</option>
                <option value="absent">Absent</option>
                <option value="important_plan">Important Plan</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit">Update Attendance</button>
        </div>
    </form>

    <h2>Current Attendance Records</h2>
    <a href="?download=excel" class="download-button">Download as Excel</a>
    
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Status</th>
                <th>Date</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($attendanceRecords)): ?>
                <tr>
                    <td colspan="4">No records found for today.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($attendanceRecords as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['name']); ?></td>
                        <td><?php echo htmlspecialchars($record['status']); ?></td>
                        <td><?php echo htmlspecialchars($record['date']); ?></td>
                        <td><?php echo date('h:i A', strtotime(htmlspecialchars($record['time']))); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Include Font Awesome for Icons -->

</body>
</html>
