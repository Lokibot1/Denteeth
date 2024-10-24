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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="in.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Meddon&display=swap"
    rel="stylesheet">

<title>Login</title>

</head>
<body>
<nav>
    <a href="Home_page.php">
      <div class="logo">
        <h1>EHM Dental Clinic</h1>
      </div>
    </a>
    <a id="back" href="../login.php">
    →
    </a>
</nav>

<center>
  <div class="login">
    <div class="form">
        <h1>LOG IN</h1>
        <form action="login.php" method="POST">
            <label for="username"></label>
            <input type="text" id="username" name="username" placeholder="Username" required><br><br>
            <label for="password"></label>
            <input type="password" id="password" name="password" placeholder="Password" required><br><br>
            <button type="submit">SIGN IN</button>
        </form>
    </div>

    <div id="s-bx">
      <div class="blk">
        <div class="s-img">
            <img src="HOME_PAGE/img/logo.png" alt="Logo">
        </div>
        <h1>EHM</h1>
        <h2>Dental Clinic<br> ┈┈┈┈┈┈ <br> Laboratory</h2>
        <h3>Life's fair with Dental Care</h3>
    </div>
    </div>
  </div>
</center>

</body>
</html>