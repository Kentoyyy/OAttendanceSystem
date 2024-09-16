<?php
// Database connection
include('db_connect.php');

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form values
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $dob = $_POST['dob'];
    $location = $_POST['location'];
    $user_type = $_POST['user_type'];

    // Validate the inputs (optional)
    if (!empty($name) && !empty($email) && !empty($contact) && !empty($dob) && !empty($location) && !empty($user_type)) {
        // Prepare SQL query for inserting user
        $stmt = $conn->prepare("INSERT INTO users (name, email, contact, dob, location, user_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $contact, $dob, $location, $user_type);

        // Execute query
        if ($stmt->execute()) {
            // Success message
            header('Location: user_management.php?success=1');
        } else {
            // Error handling
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Please fill all required fields.";
    }

    // Close the connection
    $conn->close();
}
?>
