<?php
$servername = "localhost";
$dbUsername = "root";  // Replace with your database username
$dbPassword = "";      // Replace with your database password
$dbname = "system_attendancedb"; // Replace with your chosen database name

// Create connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the correct charset for the database connection
if (!$conn->set_charset("utf8mb4")) {
    die("Error loading character set utf8mb4: " . $conn->error);
}
?>
