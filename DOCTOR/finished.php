<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['1', '2'])) {
    header("Location: ../login.php");
    exit();
}

include("../dbcon.php");

// Check database connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle update request
if (isset($_POST['update'])) {
    // Get form data from modal
    $id = $_POST['id'];
    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
    $middle_name = mysqli_real_escape_string($con, $_POST['middle_name']);
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $modified_date = mysqli_real_escape_string($con, $_POST['modified_date']);
    $modified_time = mysqli_real_escape_string($con, $_POST['modified_time']);
    $service_type = mysqli_real_escape_string($con, $_POST['service_type']);

    // Update query for tbl_patient
    $update_patient_query = "UPDATE tbl_patient 
                             SET first_name='$first_name', middle_name='$middle_name', last_name='$last_name'
                             WHERE id=$id";

    // Update query for tbl_appointments
    $update_appointment_query = "UPDATE tbl_appointments 
                                 SET contact='$contact', modified_date='$modified_date', modified_time='$modified_time', modified_by = '2', service_type='$service_type' 
                                 WHERE id=$id";  // Assuming patient_id is used as foreign key in tbl_appointments

    // Execute both queries
    if (mysqli_query($con, $update_patient_query) && mysqli_query($con, $update_appointment_query)) {
        // Redirect to the same page after updating
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}


if (isset($_POST['finish'])) {
    // Check if the connection exists
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get the appointment ID from the form
    $id = $_POST['id'];

    // Prepare the query to update the status to 'finished' using a prepared statement
    $stmt = $con->prepare("UPDATE tbl_appointments SET status=? WHERE id=?");
    $status = 4; // Assuming '4' represents finished
    $stmt->bind_param("ii", $status, $id);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect back to the dashboard
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    $stmt->close();
}

if (isset($_POST['declined'])) {
    // Check if the connection exists
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $id = $_POST['id'];

    // Fetch the appointment data to insert into appointments_bin
    $stmt = $con->prepare("SELECT * FROM tbl_appointments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $appointment = $result->fetch_assoc();

        // Insert into appointments_bin
        $insert_stmt = $con->prepare("INSERT INTO tbl_appointments_bin (name, contact, date, time, modified_date, modified_time, service_type, status)
                                      VALUES (?, ?, ?, ?, ?, ?)");
        $status = '2';
        $insert_stmt->bind_param("ssssss", $appointment['name'], $appointment['contact'], $appointment['date'], $appointment['time'], $appointment['modified_date'], $appointment['modified_time'], $appointment['service_type'], $status);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    // Delete the appointment from the appointments table
    $delete_stmt = $con->prepare("DELETE FROM tbl_appointments WHERE id=?");
    $delete_stmt->bind_param("i", $id);

    // Execute the delete query
    if ($delete_stmt->execute()) {
        // Redirect back to the dashboard
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo "Error deleting record: " . $delete_stmt->error;
    }

    $stmt->close();
    $delete_stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="doctor_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Doctor Dashboard</title>
</head>

<body>
    <!-- Navigation/Sidebar -->
    <nav>
        <a href="../HOME_PAGE/Home_page.php">
            <div class="logo">
                <h1><span>EHM</span> Dental Clinic</h1>
            </div>
        </a>
        <form method="POST" action="../logout.php">
            <button type="submit" class="logout-button">Logout</button>
        </form>
        </a>
    </nav>
    <div>
        <aside class="sidebar">
            <ul>
                <br>
                <a href="doctor_dashboard.php">
                    <h3>DOCTOR <br>DASHBOARD</h3>
                </a>
                <br>
                <br>
                <hr>
                <br>
                <li><a href="day.php">Appointment for the day</a></li>
                <li><a href="week.php">Appointment for the week</a></li>
                <li class="active"><a href="finished.php">Finished Appointments</a></li>
                <li><a href="services.php">Services</a></li>
            </ul>
        </aside>
    </div>
    <!-- Main Content/Crud -->
    <div class="top">
        <div class="content-box">
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
                              FROM tbl_appointments 
                              WHERE (DATE(date) = '$today' OR DATE(modified_date) = '$today') AND status = '3'";



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
                <p>APPOINTMENT FOR THE WEEK:</p>
                <?php
                // Get the start and end date of the current week
                $start_of_week = date('Y-m-d', strtotime('monday this week'));
                $end_of_week = date('Y-m-d', strtotime('sunday this week'));

                // Query to count appointments for the current week
                $sql_week = "SELECT COUNT(*) as total_appointments_week 
                 FROM tbl_appointments 
                 WHERE (DATE(date) BETWEEN '$start_of_week' AND '$end_of_week' 
                 OR DATE(modified_date) BETWEEN '$start_of_week' AND '$end_of_week') 
                 AND status = '3'";

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
                $sql_finished = "SELECT COUNT(*) as total_finished_appointments FROM tbl_appointments WHERE status = '4'";
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
            <?php
            // Set the number of results per page
            $resultsPerPage = 15;

            // Get the current page number from query parameters, default to 1
            $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            // Calculate the starting row for the SQL query
            $startRow = ($currentPage - 1) * $resultsPerPage;

            // SQL query to count total records
            $countQuery = "SELECT COUNT(*) as total FROM tbl_appointments WHERE status = '4'";
            $countResult = mysqli_query($con, $countQuery);
            $totalCount = mysqli_fetch_assoc($countResult)['total'];
            $totalPages = ceil($totalCount / $resultsPerPage); // Calculate total pages
            
            // SQL query with JOIN to fetch the limited number of records with OFFSET
            $query = "SELECT a.*, 
                     s.service_type AS service_name, 
                     p.first_name, p.middle_name, p.last_name,
                     t.status  -- Assuming status name is stored in tbl_status
                  FROM tbl_appointments a
                  JOIN tbl_service_type s ON a.service_type = s.id
                  JOIN tbl_patient p ON a.id = p.id  -- Ensure you're joining using id
                  JOIN tbl_status t ON a.status = t.id
                  WHERE (DATE(a.date) BETWEEN '$start_of_week' AND '$end_of_week'  
                         OR DATE(a.modified_date) BETWEEN '$start_of_week' AND '$end_of_week') 
                    AND a.status = '4'
          LIMIT $resultsPerPage OFFSET $startRow";  // Limit to 15 rows
            
            $result = mysqli_query($con, $query);
            ?>

            <!-- HTML Table -->

            <div class="pagination-container">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?>" class="pagination-btn">
                        < </a>
                        <?php endif; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?>" class="pagination-btn"> > </a>
                        <?php endif; ?>

                        <?php if ($totalCount > 15): ?>
                        <?php endif; ?>
            </div>
        </div>
        <!-- Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Type Of Service</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Prepare data for display
                        $dateToDisplay = !empty($row['modified_date']) ? $row['modified_date'] : $row['date'];
                        $timeToDisplay = !empty($row['modified_time']) ? $row['modified_time'] : $row['time'];
                        $timeToDisplayFormatted = date("h:i A", strtotime($timeToDisplay));

                        echo "<tr>
                        <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                        <td>{$row['contact']}</td>
                        <td>{$dateToDisplay}</td>
                        <td>{$timeToDisplayFormatted}</td>
                        <td>{$row['service_name']}</td>
                        <td>{$row['status']}</td></tr>"; // Display status name instead of status id
                    }
                } else {
                    echo "<tr><td colspan='6'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
</body>

</html>