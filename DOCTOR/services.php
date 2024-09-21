<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'doctor', 'dental_assistant'])) {
    header("Location: ../login.php");
    exit();
}

include("../dbcon.php"); // Your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if form is submitted
    $name = $_POST['name'];
    $description = $_POST['description'];
    $image = $_FILES['image'];

    // Directory where the images will be saved
    $target_dir = "uploads/";
    // Create the directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // File path for the uploaded image
    $target_file = $target_dir . basename($image["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the uploaded file is a valid image
    $check = getimagesize($image["tmp_name"]);
    if ($check !== false) {
        // Only allow JPG and PNG
        if ($imageFileType == "jpg" || $imageFileType == "png") {
            // Move uploaded file to the target directory
            if (move_uploaded_file($image["tmp_name"], $target_file)) {
                // Insert the card data into the database
                $sql = "INSERT INTO services (name, description, image_path) VALUES ('$name', '$description', '$target_file')";
                if (mysqli_query($con, $sql)) {
                } else {
                    echo "Error: " . $sql . "<br>" . mysqli_error($con);
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "Only JPG and PNG files are allowed.";
        }
    } else {
        echo "File is not an image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="services.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <title>Doctor Dashboard</title>
</head>

<body>
    <!-- Navigation/Sidebar -->
    <nav>
        <div class="logo-container">
            <label class="logo">Denteeth</label>
            <form method="POST" action="../logout.php">
                <button type="submit" class="logout-button">Logout</button>
            </form>
        </div>
        <div class="w3-sidebar w3-light-grey w3-bar-block custom-sidebar">
            <a href="doctor_dashboard.php">
                <h3 class="w3-bar-item">DOCTOR<br>DASHBOARD</h3>
            </a>
            <a href="day.php" class="w3-bar-item w3-button">Appointment for the day</a>
            <a href="week.php" class="w3-bar-item w3-button">Appointment for the week</a>
            <a href="finished.php" class="w3-bar-item w3-button">Finished Appointments</a>
            <a href="services.php" class="w3-bar-item w3-button active">Services</a>
            <a href="transaction_history.php" class="w3-bar-item w3-button">Transaction History</a>
        </div>
    </nav>
    <!-- Main Content/Crud -->
    <div class="content-box">
        <div class="top">
            <div class="round-box">
                <p>APPOINTMENT TODAY:</p>
                <?php
                include("../dbcon.php");

                // Set the default time zone to Hong Kong
                date_default_timezone_set('Asia/Hong_Kong');

                // Check database connection
                if (!$con) {
                    die("Connection failed: " . mysqli_connect_error());
                }

                // Get current date
                $today = date('Y-m-d');

                // Query to count appointments for today
                $sql_today = "SELECT COUNT(*) as total_appointments_today 
                              FROM appointments 
                              WHERE DATE(date) = '$today'";

                $result_today = mysqli_query($con, $sql_today);

                // Check for SQL errors
                if (!$result_today) {
                    die("Query failed: " . mysqli_error($con));
                }

                $row_today = mysqli_fetch_assoc($result_today);
                $appointments_today = $row_today['total_appointments_today'];

                echo $appointments_today ? $appointments_today : 'No data available';
                ?>
            </div>
            <div class="round-box">
                <p>PENDING APPOINTMENTS:</p>
                <?php
                // Query to count pending appointments
                $sql_pending = "SELECT COUNT(*) as total_pending_appointments 
                                FROM appointments 
                                WHERE status = 'pending'";
                $result_pending = mysqli_query($con, $sql_pending);

                // Check for SQL errors
                if (!$result_pending) {
                    die("Query failed: " . mysqli_error($con));
                }

                $row_pending = mysqli_fetch_assoc($result_pending);
                $pending_appointments = $row_pending['total_pending_appointments'];

                echo $pending_appointments ? $pending_appointments : 'No data available';
                ?>
            </div>
            <div class="round-box">
                <p>APPOINTMENT FOR THE WEEK:</p>
                <?php
                // Get the start and end date of the current week
                $start_of_week = date('Y-m-d', strtotime('monday this week'));
                $end_of_week = date('Y-m-d', strtotime('sunday this week'));

                // Query to count appointments for the current week
                $sql_week = "SELECT COUNT(*) as total_appointments_week 
                             FROM appointments 
                             WHERE DATE(date) BETWEEN '$start_of_week' AND '$end_of_week'";
                $result_week = mysqli_query($con, $sql_week);

                // Check for SQL errors
                if (!$result_week) {
                    die("Query failed: " . mysqli_error($con));
                }

                $row_week = mysqli_fetch_assoc($result_week);
                $appointments_for_week = $row_week['total_appointments_week'];

                echo $appointments_for_week ? $appointments_for_week : 'No data available';
                ?>
            </div>
            <div class="round-box">
                <p>FINISHED APPOINTMENTS:</p>
                <?php
                // Query to count finished appointments
                $sql_finished = "SELECT COUNT(*) as total_finished_appointments FROM appointments WHERE status = 'finished'";
                $result_finished = mysqli_query($con, $sql_finished);

                // Check for SQL errors
                if (!$result_finished) {
                    die("Query failed: " . mysqli_error($con));
                }

                $row_finished = mysqli_fetch_assoc($result_finished);
                $finished_appointments = $row_finished['total_finished_appointments'];

                echo $finished_appointments ? $finished_appointments : 'No data available';
                ?>
            </div>
            <h1>Services</h1>
            <div id="crvs-container">
                <div class="img-box">
                    <a href="SERVICES/Orthodontic_Braces.php">
                        <div class="img-wrapper">
                            <p>ORTHODONTIC BRACES</p>
                            <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                        </div>
                    </a>
                </div>
                <div class="img-box">
                    <a href="SERVICES/Dental_Cleaning.php">
                        <div class="img-wrapper">
                            <p>DENTAL CLEANING</p>
                            <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                        </div>
                    </a>
                </div>
                <div class="img-box">
                    <a href="SERVICES/Dental_Whitening.php">
                        <div class="img-wrapper">
                            <p>DENTAL WHITENING</p>
                            <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                        </div>
                    </a>
                </div>
                <div class="img-box">
                    <a href="SERVICES/Dental_Implants.php">
                        <div class="img-wrapper">
                            <p>DENTAL IMPLANTS</p>
                            <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                        </div>
                    </a>
                </div>
                <div class="img-box">
                    <a href="SERVICES/Restoration.php">
                        <div class="img-wrapper">
                            <p>RESTORATION</p>
                            <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                        </div>
                    </a>
                </div>
                <div class="img-box">
                    <a href="SERVICES/Extraction.php">
                        <div class="img-wrapper">
                            <p>EXTRACTION</p>
                            <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                        </div>
                    </a>
                </div>
                <div class="img-box">
                    <a href="SERVICES/All_Porcelain_Veneers_&_Zirconia.php">
                        <div class="img-wrapper">
                            <p>ALL PORCELAIN VENEER & ZIRCONIA</p>
                            <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                        </div>
                    </a>
                </div>
                <div class="img-box">
                    <a href="SERVICES/Full_Exam_&_X-Ray.php">
                        <div class="img-wrapper">
                            <p>FULL EXAM & X-RAY</p>
                            <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                        </div>
                    </a>
                </div>
                <div class="img-box">
                    <a href="SERVICES/Root_Canal_Treatment.php">
                        <div class="img-wrapper">
                            <p>ROOT CANAL TREATMENT</p>
                            <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                        </div>
                    </a>
                </div>
                <div class="img-box">
                    <a href="SERVICES/Dentures.php">
                        <div class="img-wrapper">
                            <p>DENTURE</p>
                            <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                        </div>
                    </a>
                </div>
                <div class="img-box">
                    <a href="SERVICES/Crown_&_Bridge.php">
                        <div class="img-wrapper">
                            <p>CROWN & BRIDGE</p>
                            <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                        </div>
                    </a>
                </div>
            </div>
</body>

</html>