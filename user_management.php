<?php
include 'db_connection.php';

// Handle form submission for adding a user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $dob = $_POST['dob'];
    $location = $_POST['location']; // New field

    $stmt = $conn->prepare("INSERT INTO users (name, email, contact, dob, location) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $contact, $dob, $location);

    if ($stmt->execute()) {
        $action = "Added new user: $name";
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
    $location = $_POST['location']; // New field

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, contact = ?, dob = ?, location = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $email, $contact, $dob, $location, $id);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
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
        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group input:focus {
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
        <img src="path_to_your_logo_image/logo.png" alt="Logo">
        <h2>Attendance System</h2>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="user_management.php" class="active"><i class="fas fa-users"></i> User Management</a>
        <a href="manage_attendance.php"><i class="fas fa-calendar-check"></i> Manage Attendance</a>
        <a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>
        <a href="#"><i class="fas fa-cog"></i> Settings</a>
        <a href="#"><i class="fas fa-logout"></i> Log out</a>
    </div>

    <div class="content">
        <h1>User Management</h1>
        <div class="tabs">
            <button class="active" onclick="showTab('add')">Add User</button>
            <button onclick="showTab('view')">View Users</button>
        </div>

        <!-- Add User Tab -->
        <div id="add" class="tab-content" style="display:none;">
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
                    <button type="submit" name="action" value="add">Add User</button>
                </div>
            </form>
        </div>

        <!-- View Users Tab -->
        <div id="view" class="tab-content">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Date of Birth</th>
                        <th>Location</th> <!-- New Column -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['contact']; ?></td>
                        <td><?php echo $user['dob']; ?></td>
                        <td><?php echo $user['location']; ?></td> <!-- New Field -->
                        <td class="actions">
                            <a href="javascript:void(0)" onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo $user['name']; ?>', '<?php echo $user['email']; ?>', '<?php echo $user['contact']; ?>', '<?php echo $user['dob']; ?>', '<?php echo $user['location']; ?>')">Edit</a>
                            <a href="?delete_id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal for Editing User -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <h2>Edit User</h2>
                <form id="editForm" action="user_management.php" method="post">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_name">Name</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_contact">Contact</label>
                        <input type="text" id="edit_contact" name="contact" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_dob">Date of Birth</label>
                        <input type="date" id="edit_dob" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_location">Location</label>
                        <input type="text" id="edit_location" name="location" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="action" value="edit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            document.getElementById(tabId).style.display = 'block';

            document.querySelectorAll('.tabs button').forEach(button => {
                button.classList.remove('active');
            });
            document.querySelector(`.tabs button[onclick="showTab('${tabId}')"]`).classList.add('active');
        }

        function openEditModal(id, name, email, contact, dob, location) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_contact').value = contact;
            document.getElementById('edit_dob').value = dob;
            document.getElementById('edit_location').value = location;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>
