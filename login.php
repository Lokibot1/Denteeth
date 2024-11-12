<?php
session_start();
$error_message = '';
$username_value = '';

include("dbcon.php");
// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

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

        // Verify hashed password
        if (password_verify($input_password, $stored_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role == '2') {
                header("Location: DOCTOR/doctor_dashboard.php");
            } elseif ($role == '3') {
                header("Location: DENTAL_ASSISTANT/dental_assistant_dashboard.php");
            } elseif ($role == "1") {
                header("Location: ADMIN/admin_dashboard.php");
            }
            exit();
        } else {
            $error_message = "Invalid username or password.";
        }

    $stmt->close();
    $con->close();
    }
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
    <a href="HOME_PAGE/Home_page.php">
        <div class="logo">
            <h1>EHM Dental Clinic</h1>
        </div>
    </a>
</nav>

<center>
<div class="login">
    <div class="form">
        <h1>LOG IN</h1>
        <form action="login.php" method="POST" oninput="hideErrorMessage()">
            <label for="username"></label>
            <input type="text" id="username" name="username" placeholder="Username" required><br><br>
            <label for="password"></label>
            <input type="password" id="password" name="password" placeholder="Password" required><br><br>

            <?php if (!empty($error_message)): ?>
                <p id="error-message" style="color: red; margin-bottom: 30px;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <button type="submit">SIGN IN</button>
        </form>
    </div>

    <script>
    function hideErrorMessage() {
    const errorMessage = document.getElementById('error-message');
    if (errorMessage) {
        errorMessage.style.display = 'none';
    }
}

    window.onload = function() {
    const errorMessage = document.getElementById('error-message');
    if (errorMessage) {
        errorMessage.style.display = 'block';
    }
};

</script>

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