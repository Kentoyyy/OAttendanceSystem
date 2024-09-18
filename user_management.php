<?php
include 'db_connection.php';

// Handle form submission for adding a user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $dob = $_POST['dob'];
    $location = $_POST['location'];
    $user_type = $_POST['user_type']; // New field for user type (Kid or Adult)

    $stmt = $conn->prepare("INSERT INTO users (name, email, contact, dob, location, user_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $contact, $dob, $location, $user_type);

    if ($stmt->execute()) {
        $action = "Added new user: $name as $user_type";
        $logStmt = $conn->prepare("INSERT INTO activity_log (action) VALUES (?)");
        $logStmt->bind_param("s", $action);
        $logStmt->execute();
        $logStmt->close();
        header("Location: dashboard.php?activity=added");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: dashboard.php?activity=deleted");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle user editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $dob = $_POST['dob'];
    $location = $_POST['location'];
    $user_type = $_POST['user_type']; // New field for user type (Kid or Adult)

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, contact = ?, dob = ?, location = ?, user_type = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $name, $email, $contact, $dob, $location, $user_type, $id);

    if ($stmt->execute()) {
        $action = "Updated user ID: $id";
        $logStmt = $conn->prepare("INSERT INTO activity_log (action) VALUES (?)");
        $logStmt->bind_param("s", $action);
        $logStmt->execute();
        $logStmt->close();
        header("Location: dashboard.php?activity=updated");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all users for viewing
$users = $conn->query("SELECT * FROM users");

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="images/church.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GGC | User Management</title>
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
        .tabs {
            margin-bottom: 20px;
            display: flex;
            border-bottom: 2px solid #0D7C66;
        }
        .tabs button {
            background: none;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 5px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .tabs button.active {
            background-color: #16325B;
            color: white;
        }
        .tabs button:hover {
            background-color: #55679C;
            color: white;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #0D7C66;
            outline: none;
        }
        .form-group button {
            background-color: #16325B;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .form-group button:hover {
            background-color: #55679C;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #16325B;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: #0D7C66;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="sidebar">
    <img src="images/church.png" alt="Logo">
    <h2>GGC Church</h2>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="user_management.php" class="active"><i class="fas fa-user-plus"></i> User Management</a>
    <a href="manage_attendance.php" ><i class="fas fa-calendar-check"></i> Manage Attendance</a>
   
    </div>

    <div class="content">
        <h1>User Management</h1>
        <div class="tabs">
            <button class="active" onclick="showTab('select-user-type')">Add User</button>
            <button onclick="showTab('view-users')">View Users</button>
        </div>

        <!-- Add User Step 1: Choose User Type -->
        <div id="select-user-type" class="tab-content">
            <h2>Select User Type</h2>
            <form id="userTypeForm">
                <div class="form-group">
                    <label for="userType">User Type:</label>
                    <select id="userType" name="userType" onchange="showFormBasedOnType()">
                        <option value="">-- Select --</option>
                        <option value="kid">Kid</option>
                        <option value="adult">Adult</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Add User Form for Kid -->
        <div id="add-kid" class="tab-content" style="display:none;">
            <h2>Add Kid</h2>
            <form action="user_management.php" method="post">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="contact">Contact</label>
                    <input type="text" id="contact" name="contact" required>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" required>
                </div>
                <div class="form-group">
                    <input type="hidden" name="user_type" value="kid">
                    <button type="submit" name="action" value="add">Add Kid</button>
                </div>
            </form>
        </div>

        <!-- Add User Form for Adult -->
        <div id="add-adult" class="tab-content" style="display:none;">
            <h2>Add Adult</h2>
            <form action="user_management.php" method="post">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="contact">Contact</label>
                    <input type="text" id="contact" name="contact" required>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" required>
                </div>
                <div class="form-group">
                    <input type="hidden" name="user_type" value="adult">
                    <button type="submit" name="action" value="add">Add Adult</button>
                </div>
            </form>
        </div>

        <!-- View Users Tab -->
        <div id="view-users" class="tab-content" style="display:none;">
            <h2>All Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Date of Birth</th>
                        <th>Location</th>
                        <th>User Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['contact']; ?></td>
                            <td><?php echo $row['dob']; ?></td>
                            <td><?php echo $row['location']; ?></td>
                            <td><?php echo ucfirst($row['user_type']); ?></td>
                            <td class="actions">
                                <a href="edit_user.php?id=<?php echo $row['id']; ?>">Edit</a>
                                <a href="user_management.php?delete_id=<?php echo $row['id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <footer style="background-color: #16325B; color: white; text-align: center; padding: 10px 0; margin-left: 240px; width: calc(100% - 240px);">
    <p>&copy; <?php echo date("Y"); ?> GGC Church. All rights reserved by: lalove ♡</p>
</footer>

    <script>
        function showTab(tabId) {
            var tabs = document.getElementsByClassName('tab-content');
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].style.display = 'none';
            }
            document.getElementById(tabId).style.display = 'block';

            var tabButtons = document.getElementsByClassName('tabs')[0].getElementsByTagName('button');
            for (var i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove('active');
            }
            event.target.classList.add('active');
        }

        function showFormBasedOnType() {
            var userType = document.getElementById('userType').value;
            document.getElementById('add-kid').style.display = 'none';
            document.getElementById('add-adult').style.display = 'none';

            if (userType === 'kid') {
                document.getElementById('add-kid').style.display = 'block';
            } else if (userType === 'adult') {
                document.getElementById('add-adult').style.display = 'block';
            }
        }
    </script>
</body>
</html>
