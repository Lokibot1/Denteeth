<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['1'])) {
    header("Location: ../login.php");
    exit();
}

include("../dbcon.php");

// Check database connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Admin Dashboard</title>
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
                <a class="active" href="admin_dashboard.php">
                    <h3>ADMIN<br>DASHBOARD</h3>
                </a>
                <br>
                <br>
                <hr>
                <br>
                <li><a href="appointments_bin.php">Appointments Bin</a></a></li>
                <li><a href="transaction_history_bin.php">Transaction History Bin</a></li>
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
                <p>PENDING APPOINTMENTS:</p>
                <?php
                // Query to count pending appointments
                $sql_pending = "SELECT COUNT(*) as total_pending_appointments 
                                FROM tbl_appointments 
                                WHERE status = '1'";
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
                <p>DECLINED APPOINTMENTS:</p>
                <?php
                // Query to count finished appointments
                $sql_finished = "SELECT COUNT(*) as total_finished_appointments FROM tbl_appointments WHERE status = '2'";
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
            $resultsPerPage = 20;

            // Get the current page number from query parameters, default to 1
            $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            // Calculate the starting row for the SQL query
            $startRow = ($currentPage - 1) * $resultsPerPage;

            // SQL query to count total records
            $countQuery = "SELECT COUNT(*) as total FROM tbl_appointments WHERE status IN ('1', '2', '3', '4')";
            $countResult = mysqli_query($con, $countQuery);
            $totalCount = mysqli_fetch_assoc($countResult)['total'];
            $totalPages = ceil($totalCount / $resultsPerPage); // Calculate total pages
            
            // SQL query with JOIN to fetch the limited number of records with OFFSET
            $query = "SELECT a.*, 
            s.service_type AS service_name, 
            p.first_name, p.middle_name, p.last_name,
            t.status     
          FROM tbl_appointments a
          JOIN tbl_service_type s ON a.service_type = s.id
          JOIN tbl_patient p ON a.id = p.id
          JOIN tbl_status t ON a.status = t.id
          WHERE a.status IN ('1', '2', '3', '4')
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
                    <th>Modified_Date</th>
                    <th>Modified_Time</th>
                    <th>Type Of Service</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Check if modified_date and modified_time are empty
                        $modified_date = !empty($row['modified_date']) ? $row['modified_date'] : $row['date'];
                        $modified_time = !empty($row['modified_time']) ? $row['modified_time'] : $row['time'];

                        $dateToDisplay = !empty($row['date']) ? $row['date'] : $row['date'];
                        $timeToDisplay = !empty($row['time']) ? $row['time'] : $row['time'];

                        // Format time to HH:MM AM/PM
                        $timeToDisplayFormatted = date("h:i A", strtotime($timeToDisplay));
                        $timeToDisplayFormattedd = date("h:i A", strtotime($modified_time));

                        echo "<tr>
                        <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                        <td>{$row['contact']}</td>
                        <td>{$dateToDisplay}</td>
                        <td>{$timeToDisplayFormatted}</td>
                        <td>{$modified_date}</td>
                        <td>{$timeToDisplayFormattedd}</td>
                        <td>{$row['service_name']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>