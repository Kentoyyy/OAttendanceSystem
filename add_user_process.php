<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $dob = $_POST['dob'];

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO users (name, email, contact, dob) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $contact, $dob);

    if ($stmt->execute()) {
        // Redirect to the dashboard page after successful insertion
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
