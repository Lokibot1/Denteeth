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

    // Check for conflicts in both original date/time and modified date/time
    $conflict_query = "SELECT id 
        FROM tbl_appointments 
        WHERE 
            (date = '$modified_date' AND TIME(time) = TIME('$modified_time')) OR 
            (modified_date = '$modified_date' AND TIME(modified_time) = TIME('$modified_time'))
        AND id != $id"; // Exclude the current appointment being updated

    $conflict_result = mysqli_query($con, $conflict_query);

    if (mysqli_num_rows($conflict_result) > 0) {
        // Conflict found
        echo "<script>alert('The selected date and time are already booked. Please choose a different time.');</script>";
    } else {
        // No conflict - proceed with the update

        // Update query for tbl_patient
        $update_patient_query = "UPDATE tbl_patient 
                                 SET first_name='$first_name', middle_name='$middle_name', last_name='$last_name'
                                 WHERE id=$id";

        // Update query for tbl_appointments
        $update_appointment_query = "UPDATE tbl_appointments 
                                     SET contact='$contact', modified_date='$modified_date', modified_time='$modified_time', modified_by = '1', service_type='$service_type' 
                                     WHERE id=$id";  // Assuming patient_id is used as foreign key in tbl_appointments

        // Execute both queries
        if (mysqli_query($con, $update_patient_query) && mysqli_query($con, $update_appointment_query)) {
            // Redirect to the same page after updating
            header("Location: appointments.php");
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($con);
        }
    }
}


if (isset($_POST['submit'])) {
    // Get and sanitize the posted data
    $id = intval($_POST['id']); // Appointment ID
    $note = mysqli_real_escape_string($con, $_POST['note']);
    $price = floatval($_POST['price']); // Price

    // Fetch appointment details
    $stmt = $con->prepare("SELECT * FROM tbl_appointments WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $appointment = $result->fetch_assoc();

            // Archive appointment data
            $archive_stmt = $con->prepare("INSERT INTO tbl_archives 
                (name, contact, date, time, modified_date, modified_time, service_type, note, price, completion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '1')");
            $archive_stmt->bind_param(
                "ssssssssd",
                $appointment['name'],
                $appointment['contact'],
                $appointment['date'],
                $appointment['time'],
                $appointment['modified_date'],
                $appointment['modified_time'],
                $appointment['service_type'],
                $note,
                $price
            );

            if (!$archive_stmt->execute()) {
                die("Error inserting into tbl_archives: " . $archive_stmt->error);
            }

            // Remove the appointment from tbl_appointments
            $delete_stmt = $con->prepare("DELETE FROM tbl_appointments WHERE id = ?");
            $delete_stmt->bind_param("i", $id);

            if (!$delete_stmt->execute()) {
                die("Error deleting appointment: " . $delete_stmt->error);
            }

            // Redirect to appointments page
            header("Location: appointments.php");
            exit();
        } else {
            die("Error: Appointment not found.");
        }
    } else {
        die("Error executing fetch query: " . $stmt->error);
    }
}


if (isset($_POST['decline'])) {
    $id = $_POST['id'];
    $deleteQuery = "UPDATE tbl_appointments SET status = '2' WHERE id = $id";
    mysqli_query($con, $deleteQuery);

    // Redirect to refresh the page and show updated records
    header("Location: appointments.php");
}

// SQL query to count total records
$countQuery = "SELECT COUNT(*) as total FROM tbl_appointments WHERE status = '1'";
$countResult = mysqli_query($con, $countQuery);
$totalCount = mysqli_fetch_assoc($countResult)['total'];

// SQL query with JOIN to fetch the limited number of records
$query = "SELECT a.*, 
            s.service_type AS service_name, 
            p.first_name, p.middle_name, p.last_name
          FROM tbl_appointments a
          JOIN tbl_service_type s ON a.service_type = s.id
          JOIN tbl_patient p ON a.id = p.id
          WHERE a.status = '3'
          LIMIT 15";  // Limit to 15 rows

$result = mysqli_query($con, $query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Dental Assistant Dashboard</title>
</head>

<body>
    <!-- Navigation/Sidebar -->
    <nav>
        <a href="../HOME_PAGE/Home_page.php">
            <div class="logo">
                <h1><span>EHM</span> Dental Clinic</h1>
            </div>
        </a>
        <form method="POST" class="s-buttons" action="../logout.php">
            <a href="archives.php"><i class="fas fa-trash trash"></i></a>
            <button type="submit" class="logout-button">Logout</button>
        </form>
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
                <li><a href="pending.php">Pending Appointments</a></a></li>
                <li><a href="appointments.php">Approved Appointments</a></li>
                <li><a href="declined.php">Decline Appointments</a></a></li>
                <li><a href="billing.php">Billing Approval</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="manage_user.php">Manage Users</a></li>
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
                              WHERE (
                                (modified_date IS NOT NULL AND 
                                DATE(modified_date) = CURDATE()) 
                                OR (modified_date IS NULL AND 
                                DATE(date) = CURDATE())
                                ) AND status = '3'";


                $result_today = mysqli_query($con, $sql_today);

                // Check for SQL errors
                if (!$result_today) {
                    die("Query failed: " . mysqli_error($con));
                }

                $row_today = mysqli_fetch_assoc($result_today);
                $appointments_today = $row_today['total_appointments_today'];

                if ($appointments_today) {
                    echo "<span style='color: #FF9F00; font-weight: bold; font-size: 25px;'>$appointments_today</span>";
                } else {
                    echo "<span style='color: red;'>No data available</span>";
                }
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

                if ($pending_appointments) {
                    echo "<span style='color: #FF9F00; font-weight: bold; font-size: 25px;'>$pending_appointments</span>";
                } else {
                    echo "<span style='color: red;'>No data available</span>";
                }
                ?>
            </div>
            <div class="round-box">
                <p>APPOINTMENT FOR THIS WEEK:</p>
                <?php
                // Get the start and end date of the current week
                $start_of_week = date('Y-m-d', strtotime('monday this week'));
                $end_of_week = date('Y-m-d', strtotime('sunday this week'));

                // Query to count appointments for the current week
                $sql_week = "SELECT COUNT(*) as total_appointments_week 
                 FROM tbl_appointments 
                 WHERE (
                    (modified_date IS NOT NULL AND 
                     WEEK(DATE(modified_date), 1) = WEEK(CURDATE(), 1) AND DATE(modified_date) != CURDATE())
                    OR 
                    (date IS NOT NULL AND 
                     WEEK(DATE(date), 1) = WEEK(CURDATE(), 1) AND DATE(date) > CURDATE())
                        )
                 AND status = '3'";

                $result_week = mysqli_query($con, $sql_week);

                // Check for SQL errors
                if (!$result_week) {
                    die("Query failed: " . mysqli_error($con));
                }

                $row_week = mysqli_fetch_assoc($result_week);
                $appointments_for_week = $row_week['total_appointments_week'];

                if ($appointments_for_week) {
                    echo "<span style='color: #FF9F00; font-weight: bold; font-size: 25px;'>$appointments_for_week</span>";
                } else {
                    echo "<span style='color: red;'>No data available</span>";
                }
                ?>
            </div>
            <div class="round-box">
                <p>APPOINTMENT FOR NEXT WEEK:</p>
                <?php
                // Get the start and end date of the current week
                $start_of_week = date('Y-m-d', strtotime('monday this week'));
                $end_of_week = date('Y-m-d', strtotime('sunday this week'));

                // Query to count appointments for the current week
                $sql_week = "SELECT COUNT(*) as total_appointments_week 
                 FROM tbl_appointments 
                 WHERE (
                    (modified_date IS NOT NULL AND 
                    WEEK(DATE(modified_date), 1) = WEEK(CURDATE(), 1) + 1 AND DATE(modified_date) != CURDATE())
                    OR 
                    (date IS NOT NULL AND 
                    WEEK(DATE(date), 1) = WEEK(CURDATE(), 1) + 1 AND DATE(date) > CURDATE())
                    )
                    AND status = '3'";

                $result_week = mysqli_query($con, $sql_week);

                // Check for SQL errors
                if (!$result_week) {
                    die("Query failed: " . mysqli_error($con));
                }

                $row_week = mysqli_fetch_assoc($result_week);
                $appointments_for_week = $row_week['total_appointments_week'];

                if ($appointments_for_week) {
                    echo "<span style='color: #FF9F00; font-weight: bold; font-size: 25px;'>$appointments_for_week</span>";
                } else {
                    echo "<span style='color: red;'>No data available</span>";
                }
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

                if ($finished_appointments) {
                    echo "<span style='color: #FF9F00; font-weight: bold; font-size: 25px;'>$finished_appointments</span>";
                } else {
                    echo "<span style='color: red;'>No data available</span>";
                }
                ?>
            </div>
            <?php
            // Set the number of results per page
            $resultsPerPage = 6;

            // Get the current page number from query parameters, default to 1
            $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            // Calculate the starting row for the SQL query
            $startRow = ($currentPage - 1) * $resultsPerPage;

            // Get today's date
            $today = date('Y-m-d');

            // SQL query to count total records for Day
            $countQueryDay = "SELECT COUNT(*) as total FROM tbl_appointments 
                    WHERE (
                     (modified_date IS NOT NULL AND 
                    DATE(modified_date) = CURDATE()) 
                  OR 
                  (modified_date IS NULL AND 
                    DATE(date) = CURDATE())
                    )
            AND status = '3'";
            $countResultDay = mysqli_query($con, $countQueryDay);
            $totalCountDay = mysqli_fetch_assoc($countResultDay)['total'];
            $totalPagesDay = ceil($totalCountDay / $resultsPerPage); // Calculate total pages for Day
            
            // SQL query to count total records for Week
            $start_of_week = date('Y-m-d', strtotime('last Sunday')); // Get the start of the week
            $end_of_week = date('Y-m-d', strtotime('next Saturday')); // Get the end of the week
            
            $countQueryWeek = "SELECT COUNT(*) as total FROM tbl_appointments 
                    WHERE (
                    (modified_date IS NOT NULL AND 
                     WEEK(DATE(modified_date), 1) = WEEK(CURDATE(), 1) AND DATE(modified_date) != CURDATE())
                    OR 
                    (date IS NOT NULL AND 
                     WEEK(DATE(date), 1) = WEEK(CURDATE(), 1) AND DATE(date) > CURDATE())
                        )
                    AND status = '3'";
            $countResultWeek = mysqli_query($con, $countQueryWeek);
            $totalCountWeek = mysqli_fetch_assoc($countResultWeek)['total'];
            $totalPagesWeek = ceil($totalCountWeek / $resultsPerPage); // Calculate total pages for Week
            
            $countQueryNextWeek = "SELECT COUNT(*) as total FROM tbl_appointments 
                    WHERE (
                    (modified_date IS NOT NULL AND 
                    WEEK(DATE(modified_date), 1) = WEEK(CURDATE(), 1) + 1 AND DATE(modified_date) != CURDATE())
                    OR 
                    (date IS NOT NULL AND 
                    WEEK(DATE(date), 1) = WEEK(CURDATE(), 1) + 1 AND DATE(date) > CURDATE())
                    )
                    AND status = '3'";
            $countResultNextWeek = mysqli_query($con, $countQueryNextWeek);
            $totalCountNextWeek = mysqli_fetch_assoc($countResultNextWeek)['total'];
            $totalPagesNextWeek = ceil($totalCountNextWeek / $resultsPerPage); // Calculate total pages for Week
            
            // SQL query for Day with JOIN to fetch the limited number of records with OFFSET
            $queryDay = "SELECT a.*, 
                s.service_type AS service_name, 
                p.first_name, p.middle_name, p.last_name 
            FROM tbl_appointments a
            JOIN tbl_service_type s ON a.service_type = s.id
            JOIN tbl_patient p ON a.id = p.id  -- corrected join condition
            WHERE (
                  (a.modified_date IS NOT NULL AND 
                    DATE(a.modified_date) = CURDATE()) 
                  OR 
                  (a.modified_date IS NULL AND 
                    DATE(a.date) = CURDATE())
            )
            AND a.status = '3'
            ORDER BY  a.time DESC, a.modified_time DESC
            LIMIT $resultsPerPage OFFSET $startRow";

            // SQL query for Week with JOIN to fetch the limited number of records with OFFSET
            $queryWeek = "SELECT a.*, 
                      s.service_type AS service_name, 
                      p.first_name, p.middle_name, p.last_name 
              FROM tbl_appointments a
              JOIN tbl_service_type s ON a.service_type = s.id
              JOIN tbl_patient p ON a.id = p.id  -- corrected join condition
              WHERE (
                    (a.modified_date IS NOT NULL AND 
                     WEEK(DATE(a.modified_date), 1) = WEEK(CURDATE(), 1) AND DATE(a.modified_date) != CURDATE())
                    OR 
                    (a.date IS NOT NULL AND 
                     WEEK(DATE(a.date), 1) = WEEK(CURDATE(), 1) AND DATE(a.date) > CURDATE())
              )
              AND a.status = '3'
              ORDER BY a.date DESC, a.time DESC, a.modified_date DESC, a.modified_time DESC
              LIMIT $resultsPerPage OFFSET $startRow";

            $queryNextWeek = "SELECT a.*, 
            s.service_type AS service_name, 
                      p.first_name, p.middle_name, p.last_name 
              FROM tbl_appointments a
              JOIN tbl_service_type s ON a.service_type = s.id
              JOIN tbl_patient p ON a.id = p.id  -- corrected join condition
              WHERE (
                (a.modified_date IS NOT NULL AND 
                WEEK(DATE(a.modified_date), 1) = WEEK(CURDATE(), 1) + 1 AND DATE(a.modified_date) != CURDATE())
                OR 
                (a.date IS NOT NULL AND 
                WEEK(DATE(a.date), 1) = WEEK(CURDATE(), 1) + 1 AND DATE(a.date) > CURDATE())
                )
              AND a.status = '3'
              ORDER BY a.date DESC, a.time DESC, a.modified_date DESC, a.modified_time DESC
              LIMIT $resultsPerPage OFFSET $startRow";


            $resultNextWeek = mysqli_query($con, $queryNextWeek);
            $resultWeek = mysqli_query($con, $queryWeek);
            $resultDay = mysqli_query($con, $queryDay);

            // Default tab is 'Day'
            $activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'Day';
            ?>

            <!-- Tab structure -->
            <div class="tab">
                <button class="tablinks" onclick="switchTab('Day')">Today</button>
                <button class="tablinks" onclick="switchTab('Week')">This Week</button>
                <button class="tablinks" onclick="switchTab('NextWeek')">Next Week</button>
            </div>

            <!-- Tab content for Day -->
            <div id="Day" class="tabcontent" style="display: <?php echo $activeTab == 'Day' ? 'block' : 'none'; ?>;">
                <br>
                <h3 style="color: #fff;">Today</h3>

                <!-- Pagination for Day -->
                <div class="pagination-container">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>&tab=Day" class="pagination-btn">&lt;</a>
                    <?php endif; ?>
                    <?php if ($currentPage < $totalPagesDay): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>&tab=Day" class="pagination-btn">&gt;</a>
                    <?php endif; ?>
                </div>

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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($resultDay) > 0) {
                            while ($row = mysqli_fetch_assoc($resultDay)) {
                                $modified_date = !empty($row['modified_date']) ? $row['modified_date'] : 'N/A';
                                $modified_time = !empty($row['modified_time']) ? date("h:i A", strtotime($row['modified_time'])) : 'N/A';
                                $dateToDisplay = !empty($row['date']) ? $row['date'] : 'N/A';
                                $timeToDisplay = !empty($row['time']) ? date("h:i A", strtotime($row['time'])) : 'N/A';

                                echo "<tr>
                        <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                        <td>{$row['contact']}</td>
                        <td>{$dateToDisplay}</td>
                        <td>{$timeToDisplay}</td>
                        <td>{$modified_date}</td>
                        <td>{$modified_time}</td>
                        <td>{$row['service_name']}</td>
                        <td>
                            <button type='button' onclick='openModal({$row['id']}, \"{$row['first_name']}\", \"{$row['middle_name']}\", \"{$row['last_name']}\", \"{$row['contact']}\", \"{$dateToDisplay}\", \"{$timeToDisplay}\", \"{$row['service_name']}\")' 
                             style='background-color:#083690; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>Update</button>
                            <form method='POST' action='' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <input type='submit' name='decline' value='Decline' onclick=\"return confirm('Are you sure you want to remove this record?');\" 
                            style='background-color: rgb(196, 0, 0); color:white; border:none;  padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
                            </form>";

                                if ($row['status'] != 'finished') {
                                    echo "<button type='button' onclick='openFinishModal({$row['id']}, \"{$row['first_name']}\", \"{$row['middle_name']}\", \"{$row['last_name']}\", \"{$row['contact']}\", \"{$dateToDisplay}\", \"{$timeToDisplay}\", \"{$row['service_name']}\")' 
                    style='background-color:green; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>Finish</button>";
                                }

                                echo "</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Tab content for Week -->
            <div id="Week" class="tabcontent" style="display: <?php echo $activeTab == 'Week' ? 'block' : 'none'; ?>;">
                <br>
                <h3 style="color: #fff;">This Week</h3>
                <!-- Pagination for Week -->
                <div class="pagination-container">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>&tab=Week" class="pagination-btn">&lt;</a>
                    <?php endif; ?>
                    <?php if ($currentPage < $totalPagesWeek): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>&tab=Week" class="pagination-btn">&gt;</a>
                    <?php endif; ?>
                </div>

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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($resultWeek) > 0) {
                            while ($row = mysqli_fetch_assoc($resultWeek)) {
                                $modified_date = !empty($row['modified_date']) ? $row['modified_date'] : 'N/A';
                                $modified_time = !empty($row['modified_time']) ? date("h:i A", strtotime($row['modified_time'])) : 'N/A';
                                $dateToDisplay = !empty($row['date']) ? $row['date'] : 'N/A';
                                $timeToDisplay = !empty($row['time']) ? date("h:i A", strtotime($row['time'])) : 'N/A';

                                echo "<tr>
                        <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                        <td>{$row['contact']}</td>
                        <td>{$dateToDisplay}</td>
                        <td>{$timeToDisplay}</td>
                        <td>{$modified_date}</td>
                        <td>{$modified_time}</td>
                        <td>{$row['service_name']}</td>
                        <td>
                            <button type='button' onclick='openModal({$row['id']}, \"{$row['first_name']}\", \"{$row['middle_name']}\", \"{$row['last_name']}\", \"{$row['contact']}\", \"{$dateToDisplay}\", \"{$timeToDisplay}\", \"{$row['service_name']}\")' 
                             style='background-color:#083690; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>Update</button>
                            <form method='POST' action='' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <input type='submit' name='decline' value='Decline' onclick=\"return confirm('Are you sure you want to remove this record?');\" 
                            style='background-color: rgb(196, 0, 0); color:white; border:none;  padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
                            </form>";

                                if ($row['status'] != 'finished') {
                                    echo "<button type='button' onclick='openFinishModal({$row['id']}, \"{$row['first_name']}\", \"{$row['middle_name']}\", \"{$row['last_name']}\", \"{$row['contact']}\", \"{$dateToDisplay}\", \"{$timeToDisplay}\", \"{$row['service_name']}\")' 
                    style='background-color:green; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>Finish</button>";
                                }

                                echo "</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                    </tbody>
                </table>
            </div>

            <!-- Tab content for Next Week -->
            <div id="NextWeek" class="tabcontent"
                style="display: <?php echo $activeTab == 'NextWeek' ? 'block' : 'none'; ?>;">
                <br>
                <h3 style=" color: #fff;">Next Week</h3>
                <!-- Pagination for Week -->
                <div class="pagination-container">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>&tab=NextWeek" class="pagination-btn">&lt;</a>
                    <?php endif; ?>
                    <?php if ($currentPage < $totalPagesNextWeek): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>&tab=NextWeek" class="pagination-btn">&gt;</a>
                    <?php endif; ?>
                </div>

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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($resultNextWeek) > 0) {
                            while ($row = mysqli_fetch_assoc($resultNextWeek)) {
                                $modified_date = !empty($row['modified_date']) ? $row['modified_date'] : 'N/A';
                                $modified_time = !empty($row['modified_time']) ? date("h:i A", strtotime($row['modified_time'])) : 'N/A';
                                $dateToDisplay = !empty($row['date']) ? $row['date'] : 'N/A';
                                $timeToDisplay = !empty($row['time']) ? date("h:i A", strtotime($row['time'])) : 'N/A';

                                echo "<tr>
                        <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                        <td>{$row['contact']}</td>
                        <td>{$dateToDisplay}</td>
                        <td>{$timeToDisplay}</td>
                        <td>{$modified_date}</td>
                        <td>{$modified_time}</td>
                        <td>{$row['service_name']}</td>
                        <td>
                            <button type='button' onclick='openModal({$row['id']}, \"{$row['first_name']}\", \"{$row['middle_name']}\", \"{$row['last_name']}\", \"{$row['contact']}\", \"{$dateToDisplay}\", \"{$timeToDisplay}\", \"{$row['service_name']}\")' 
                             style='background-color:#083690; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>Update</button>
                            <form method='POST' action='' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <input type='submit' name='decline' value='Decline' onclick=\"return confirm('Are you sure you want to remove this record?');\" 
                            style='background-color: rgb(196, 0, 0); color:white; border:none;  padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
                            </form>";

                                if ($row['status'] != 'finished') {
                                    echo "<button type='button' onclick='openFinishModal({$row['id']}, \"{$row['first_name']}\", \"{$row['middle_name']}\", \"{$row['last_name']}\", \"{$row['contact']}\", \"{$dateToDisplay}\", \"{$timeToDisplay}\", \"{$row['service_name']}\")' 
                    style='background-color:green; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>Finish</button>";
                                }

                                echo "</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                    </tbody>
                </table>
            </div>

            <div id="finishModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <button class="close">&times;</button>
                    <h3 style="text-align: center; font-size: 30px;">Service Completion</h3>
                    <hr>
                    <div id="modalDetails">
                        <p><strong>Name:</strong> <span id="modalName"></span></p>
                        <p><strong>Contact Number:</strong> <span id="modalContact"></span></p>
                        <p><strong>Date & Time:</strong> <span id="modalDateTime"></span></p>
                        <p><strong>Current Service:</strong> <span id="modalService"></span></p>
                    </div>
                    <hr>
                    <form id="newServiceForm" method="POST" action="">
                        <input type="hidden" name="id" value="">
                        <br>
                        <label style="font-size: 20px; font-weight: bold;" for="note">Note:</label>
                        <br>
                        <br>
                        <textarea id="note" name="note"
                            placeholder="Enter your note here..."></textarea>
                            <br>
                        <div id="totalPriceContainer">
                            <p><strong>Total Price: ₱</strong><span id="totalPrice"
                                    style="font-weight: bold; font-size: 25px;">0</span></p>
                        </div>
                        <br>
                        <input type="number" id="price" name="price" style="display: none;" readonly>
                        <button type="submit" name="submit">Proceed to Dental Assistant</button>
                    </form>
                </div>
            </div>

            <script>
                const servicePrices = {
                    1: 30000, 2: 30000, 3: 2000, 4: 100000, 5: 20000,
                    6: 30000, 7: 1500, 8: 2000, 9: 280000, 10: 40000, 11: 40000
                };

                function openFinishModal(id, firstName, middleName, lastName, contact, date, time, service) {
                    document.getElementById('modalName').innerText = `${lastName}, ${firstName} ${middleName}`;
                    document.getElementById('modalContact').innerText = contact;
                    document.getElementById('modalDateTime').innerText = `${date} at ${time}`;
                    document.getElementById('modalService').innerText = service;

                    const servicePrice = servicePrices[getServiceIdFromName(service)] || 0;
                    document.getElementById('price').value = servicePrice;
                    document.getElementById('totalPrice').innerText = servicePrice;

                    document.querySelector("#newServiceForm input[name='id']").value = id;
                    document.getElementById('finishModal').style.display = 'block';
                }

                function getServiceIdFromName(serviceName) {
                    const services = {
                        "All Porcelain Veneers & Zirconia": 1, "Crown & Bridge": 2, "Dental Cleaning": 3,
                        "Dental Implants": 4, "Dental Whitening": 5, "Dentures": 6,
                        "Extraction": 7, "Full Exam & X-Ray": 8, "Orthodontic Braces": 9,
                        "Restoration": 10, "Root Canal Treatment": 11
                    };
                    return services[serviceName] || null;
                }

                document.querySelector('.close').addEventListener('click', () => {
                    document.getElementById('finishModal').style.display = 'none';
                });

                window.addEventListener('click', (event) => {
                    if (event.target == document.getElementById('finishModal')) {
                        document.getElementById('finishModal').style.display = 'none';
                    }
                });
            </script>

            <!-- Edit Modal -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <form method="POST" action="">
                        <h1>EDIT DETAILS</h1><br>
                        <input type="hidden" name="id" id="modal-id">
                        <br>
                        <label for="modal-first-name">First Name:</label>
                        <input type="text" name="first_name" id="modal-first-name" required>
                        <br>
                        <label for="modal-last-name">Last Name:</label>
                        <input type="text" name="last_name" id="modal-last-name" required>
                        <br>
                        <label for="modal-middle-name">Middle Name:</label>
                        <input type="text" name="middle_name" id="modal-middle-name" required>
                        <br>
                        <label for="contact">Contact:</label>
                        <input type="text" name="contact" id="modal-contact" placeholder="Enter your contact number"
                            maxlength="11" required pattern="\d{11}" title="Please enter exactly 11 digits"><br>
                        <label for="date">Date:</label>
                        <input type="date" name="modified_date" id="modal-modified_date" required>
                        <br>
                        <label for="time">Time: <br> (Will only accept appointments from 9:00 a.m to 6:00 p.m)</label>
                        <select name="modified_time" id="modal-modified_time" required>
                            <option value="09:00 AM">09:00 AM</option>
                            <option value="10:30 AM">10:30 AM</option>
                            <option value="12:00 PM" disabled>12:00 AM (Lunch Break)</option>
                            <option value="12:30 PM">12:30 PM</option>
                            <option value="13:30 PM">01:30 PM</option>
                            <option value="15:00 PM">03:00 PM</option>
                            <option value="16:30 PM">04:30 PM</option>
                        </select>
                        <label for="service_type">Type Of Service:</label>
                        <select name="service_type" id="modal-service_type" required>
                            <option value="">--Select Service Type--</option>
                            <option value="1">All Porcelain Veneers & Zirconia</option>
                            <option value="2">Crown & Bridge</option>
                            <option value="3">Dental Cleaning</option>
                            <option value="4">Dental Implants</option>
                            <option value="5">Dental Whitening</option>
                            <option value="6">Dentures</option>
                            <option value="7">Extraction</option>
                            <option value="8">Full Exam & X-Ray</option>
                            <option value="9">Orthodontic Braces</option>
                            <option value="10">Restoration</option>
                            <option value="11">Root Canal Treatment</option>
                        </select>
                        <br>
                        <input type="submit" name="update" value="Save">
                    </form>
                </div>
            </div>

            <script>
                // Open the modal and populate it with data
                function openModal(id, first_name, middle_name, last_name, contact, modified_date, modified_time, service_type) {
                    document.getElementById('modal-id').value = id;
                    document.getElementById('modal-first-name').value = first_name;
                    document.getElementById('modal-middle-name').value = middle_name;
                    document.getElementById('modal-last-name').value = last_name;
                    document.getElementById('modal-contact').value = contact;
                    document.getElementById('modal-modified_date').value = modified_date;
                    document.getElementById('modal-modified_time').value = modified_time;
                    document.getElementById('modal-service_type').value = service_type;
                    document.getElementById('editModal').style.display = 'block';
                }

                // Close the modal
                function closeModal() {
                    document.getElementById('editModal').style.display = 'none';
                }

                // Switch between tabs
                function openTab(evt, tabName) {
                    var i, tabcontent, tablinks;

                    // Hide all tab content
                    tabcontent = document.getElementsByClassName("tabcontent");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }

                    // Remove 'active' class from all tab links
                    tablinks = document.getElementsByClassName("tablinks");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].classList.remove("active");
                    }

                    // Display the clicked tab's content and add 'active' class to the clicked tab
                    document.getElementById(tabName).style.display = "block";
                    evt.currentTarget.classList.add("active");
                }

                function switchTab(tabName) {
                    // Update the URL to reflect the selected tab without reloading
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tabName);
                    window.history.pushState({}, '', url);
                    // Call openTab to display the selected tab content
                    openTab(event, tabName);
                }

                // This runs when the page is loaded, ensuring the correct tab is shown based on the URL
                window.onload = function () {
                    const params = new URLSearchParams(window.location.search);
                    const activeTab = params.get('tab') || 'Day';
                    openTab({ currentTarget: document.querySelector(`[onclick="switchTab('${activeTab}')"]`) }, activeTab);
                };
            </script>
        </div>
    </div>
</body>

</html>