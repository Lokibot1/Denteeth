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
    <link rel="stylesheet" href="signup.css">
    <title>LifeLine</title>
    <div class="img-area"></div>
</head>

<body>
    <div class="container">
        <div class="armand">
            <form class="login-form" action="" method="post">
                <input type="text" name="fname" placeholder="First Name" required>
                <input type="text" name="lname" placeholder="Last Name" required>
                <select name="usertype" aria-placeholder="Type of User">
                    <option value="Admin">Admin</option>
                    <option value="User">User</option>
                </select>
                <input type="text" name="contact" placeholder="Contact" required>
                <input type="text" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Sign up">
            </form>
        </div>
    </div>
    <div>
        <a href="login.php">
            <button>Go to Login</button>
        </a>
    </div>
</body>

</html>