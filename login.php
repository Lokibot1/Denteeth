<?php
session_start();

include("dbcon.php");
// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$show_password = ''; // Variable to store the password for displaying

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Prepare and execute SQL statement
    $stmt = $con->prepare("SELECT id, password, role FROM tbl_users WHERE username = ?");
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $stored_password, $role);
        $stmt->fetch();

        // Check password
        if ($input_password == $stored_password) {
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role == '2') {
                header("Location: DOCTOR/doctor_dashboard.php");
            } else if ($role == '3') {
                header("Location: DENTAL_ASSISTANT/dental_assistant_dashboard.php");
            } else if ($role == "1") {
                header("Location: ADMIN/admin_dashboard.php");
            }
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with this username.";
    }

    $stmt->close();
    $con->close();
}

// For displaying password (Example purpose only)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['username'])) {
    $stmt = $con->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $_GET['username']);
    $stmt->execute();
    $stmt->bind_result($show_password);
    $stmt->fetch();
    $stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>
    <h1>Login</h1>
    <form action="login.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($show_password); ?>"
            required><br><br>
        <button type="submit">Login</button>
    </form>
</body>

</html>