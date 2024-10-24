<?php
session_start();

include("dbcon.php");

if($_SERVER['REQUEST_METHOD'] == "POST")
{
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $usertype = $_POST['usertype'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email already exists
    $emailCheckQuery = "SELECT * FROM signup WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($con, $emailCheckQuery);

    if (mysqli_num_rows($result) > 0) {
        echo "<script type='text/javascript'> alert('Email already registered. Please use a different email.')</script>";
    } else {
        if (!empty($email) && !empty($password) && !is_numeric($email)) {
            // Insert new user if email does not exist
            $query = "INSERT INTO signup (fname, lname, usertype, contact, email, password) VALUES ('$fname', '$lname', '$usertype', '$contact', '$email', '$password')";
            mysqli_query($con, $query);

            echo "<script type='text/javascript'> alert('Successfully Registered')</script>";
        } else {
            echo "<script type='text/javascript'> alert('Please Try Again')</script>";  
        }
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
  <div class="signup">
    <div class="form">
        <h1>Add User</h1>
            <form class="login-form" action="" method="post">
                <input type="text" name="fname" placeholder="First Name" required>
                <input type="text" name="lname" placeholder="Last Name" required><br>
                <select name="usertype" aria-placeholder="Type of User">
                    <option value="NONE">--SELECT--</option>
                    <option value="Admin">Admin</option>
                    <option value="Doctor">Doctor</option>
                    <option value="DentalAssistant">Dental Assistant</option>
                </select><br>
                <input type="text" name="email" placeholder="Email" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <input type="submit" value="Add User">
            </form>
            <div>
        <a href="login.php">
            <button class="gtlogin">Go to Login</button>
        </a>
    </div>
        </div>
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

</body>

</html>