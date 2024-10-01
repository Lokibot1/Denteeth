<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'doctor', 'dental_assistant'])) {
    header("Location: ../login.php");
    exit();
}

include("../dbcon.php");

// Check database connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle update request for appointment status
if (isset($_POST['action'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];

    // Prepare the query based on the action
    if ($action === 'Restore') {
        // Update the status of the appointment to 'pending'
        $update_query = "UPDATE appointments SET status='pending' WHERE id=$id";
    } else if ($action === 'Delete') {
        // Fetch the appointment data to insert into deleted_appointments
        $fetch_query = "SELECT * FROM appointments WHERE id=$id";
        $result = mysqli_query($con, $fetch_query);
        if ($result && mysqli_num_rows($result) > 0) {
            $appointment = mysqli_fetch_assoc($result);

            // Insert into deleted_appointments
            $insert_query = "INSERT INTO appointments_bin (fname, contact, date, time, service_type, status)
                             VALUES ('{$appointment['fname']}', '{$appointment['contact']}', '{$appointment['date']}', '{$appointment['time']}', '{$appointment['service_type']}', 'deleted')";
            mysqli_query($con, $insert_query);
        }
        // Delete the appointment from appointments table
        $delete_query = "DELETE FROM appointments WHERE id=$id";
    }

    // Execute the appropriate query
    if (
        isset($update_query) && mysqli_query($con, $update_query) ||
        isset($delete_query) && mysqli_query($con, $delete_query)
    ) {
        // Redirect to the same page after updating
        header("Location: declined.php"); // Corrected line
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dental_assistant_dashboard.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <title>dental Assistant Dashboard</title>
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
            <a href="dental_assistant_dashboard.php">
                <h3 class="w3-bar-item">DENTAL ASSISTANT<br>DASHBOARD</h3>
            </a>
            <a href="pending.php" class="w3-bar-item w3-button">Pending Appointments</a>
            <a href="day.php" class="w3-bar-item w3-button">Appointment for the Day</a>
            <a href="week.php" class="w3-bar-item w3-button">Appointment for the week</a>
            <a href="declined.php" class="w3-bar-item w3-button active">Declined Appointment</a>
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
            <!-- HTML Table -->
            <?php
            // Set the default time zone to Hong Kong
            date_default_timezone_set('Asia/Hong_Kong');

            // Get the start and end date of the current week
            $start_of_week = date('Y-m-d', strtotime('monday this week'));
            $end_of_week = date('Y-m-d', strtotime('sunday this week'));

            // Fetch only pending appointments
            $result = mysqli_query($con, "SELECT * FROM appointments WHERE status = 'declined'");

            // Loop through each appointment record
            ?>
            <div>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Type Of Service</th>
                        <th>Actions</th>
                    </tr>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                    <td>{$row['fname']}</td>
                    <td>{$row['contact']}</td>
                    <td>{$row['date']}</td>
                    <td>{$row['time']}</td>
                    <td>{$row['service_type']}</td>
                    <td>
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <button type='submit' name='action' value='Restore'>Restore</button>
                        </form>
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <button type='submit' name='action' value='Delete'>Delete</button>
                        </form>
                    </td>
                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No pending appointments found</td></tr>";
                    }

                    mysqli_close($con);
                    ?>
                </table>
            </div>
        </div>
    </div>
</body>

</html>