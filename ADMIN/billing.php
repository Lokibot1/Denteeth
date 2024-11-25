<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['1'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
include("../dbcon.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id']; // Assuming 'id' is passed in the form data

    // Initialize variables
    $service_type = '';
    $name = '';
    $contact = '';
    $date = '';
    $time = '';

    // Fetch the service details from tbl_archives
    $query = "SELECT name, contact, date, time, service_type FROM tbl_archives WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        die("SQL Error (Prepare Failed): " . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        die("SQL Error (Execute Failed): " . mysqli_error($con));
    }

    mysqli_stmt_bind_result($stmt, $name, $contact, $date, $time, $service_type);
    if (!mysqli_stmt_fetch($stmt)) {
        die("SQL Error (Fetch Failed or No Record Found): " . mysqli_error($con));
    }

    mysqli_stmt_close($stmt);

    if ($service_type == 9) { // Correct comparison operator
        // Define financial variables
        $bill = 50000.00;
        $paid = 15000.00;
        $outstanding_balance = 35000.00;

        // Insert record into tbl_transaction_history
        $insertQuery = "INSERT INTO tbl_transaction_history (id, name, contact, date, service_type, bill, paid, outstanding_balance) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = mysqli_prepare($con, $insertQuery);

        if (!$insertStmt) {
            die("SQL Error (Prepare Failed for Insert): " . mysqli_error($con));
        }

        mysqli_stmt_bind_param($insertStmt, 'isssdddd', $id, $name, $contact, $date, $service_type, $bill, $paid, $outstanding_balance);
        if (!mysqli_stmt_execute($insertStmt)) {
            die("SQL Error (Execute Failed for Insert): " . mysqli_error($con));
        }

        mysqli_stmt_close($insertStmt);

        // Delete record from tbl_archives
        $deleteQuery = "DELETE FROM tbl_archives WHERE id = ?";
        $deleteStmt = mysqli_prepare($con, $deleteQuery);

        if (!$deleteStmt) {
            die("SQL Error (Prepare Failed for Delete): " . mysqli_error($con));
        }

        mysqli_stmt_bind_param($deleteStmt, 'i', $id);
        if (!mysqli_stmt_execute($deleteStmt)) {
            die("SQL Error (Execute Failed for Delete): " . mysqli_error($con));
        }

        mysqli_stmt_close($deleteStmt);

        echo "Record successfully transferred to transaction history and deleted from archives.";
    } else {
        // Update completion to 2 in tbl_archives if not service_type = '9'
        $updateQuery = "UPDATE tbl_archives SET completion = 2 WHERE id = ?";
        $updateStmt = mysqli_prepare($con, $updateQuery);

        if (!$updateStmt) {
            die("SQL Error (Prepare Failed for Update): " . mysqli_error($con));
        }

        mysqli_stmt_bind_param($updateStmt, 'i', $id);
        if (!mysqli_stmt_execute($updateStmt)) {
            die("SQL Error (Execute Failed for Update): " . mysqli_error($con));
        }

        mysqli_stmt_close($updateStmt);

        echo "Completion status updated to 2 in archives.";
    }
}

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
        $resultsPerPage = 7;

        // Get the current page number from query parameters, default to 1
        $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        // Calculate the starting row for the SQL query
        $startRow = ($currentPage - 1) * $resultsPerPage;

        // SQL query to count total records
        $countQuery = "SELECT COUNT(*) as total FROM tbl_archives WHERE completion = 1";
        $countResult = mysqli_query($con, $countQuery);
        $totalCount = mysqli_fetch_assoc($countResult)['total'];
        $totalPages = ceil($totalCount / $resultsPerPage); // Calculate total pages
        
        $query = "SELECT a.*, 
            s.service_type AS service_name, 
            p.first_name, p.middle_name, p.last_name  
        FROM tbl_archives a
        JOIN tbl_service_type s ON a.service_type = s.id
        JOIN tbl_patient p ON a.name = p.id 
        WHERE a.completion = 1
        ORDER BY a.date DESC, a.time DESC, a.modified_date DESC, a.modified_time DESC
        LIMIT $resultsPerPage OFFSET $startRow";

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
        <!-- Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th style="font-size: 15px;">Rescheduled Date</th>
                    <th style="font-size: 15px;">Rescheduled Time</th>
                    <th>Type of Service</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Note</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Validate modified_date and modified_time
                        $modified_date = (!empty($row['modified_date']) && $row['modified_date'] !== '0000-00-00') ? $row['modified_date'] : 'N/A';
                        $modified_time = (!empty($row['modified_time']) && $row['modified_time'] !== '00:00:00') ? date("h:i A", strtotime($row['modified_time'])) : 'N/A';

                        // Validate date and time
                        $dateToDisplay = (!empty($row['date']) && $row['date'] !== '0000-00-00') ? $row['date'] : 'N/A';
                        $timeToDisplay = (!empty($row['time']) && $row['time'] !== '00:00:00') ? date("h:i A", strtotime($row['time'])) : 'N/A';

                        $priceToDisplay = isset($row['price']) ? number_format($row['price']) : 'N/A';

                        // Translate ENUM values for completion
                        $completionStatus = 'Unknown';
                        if (isset($row['completion'])) {
                            switch ($row['completion']) {
                                case '1':
                                    $completionStatus = 'Pending';
                                    break;
                                case '2':
                                    $completionStatus = 'One-time Payment';
                                    break;
                                case '3':
                                    $completionStatus = 'Package Payment';
                                    break;
                            }
                        }

                        echo "<tr>
                    <td style='width:200px;'>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                    <td>{$row['contact']}</td>
                    <td>{$dateToDisplay}</td>
                    <td>{$timeToDisplay}</td>
                    <td>{$modified_date}</td>
                    <td>{$modified_time}</td>
                    <td>{$row['service_name']}</td>
                    <td>{$completionStatus}</td>
                    <td>{$priceToDisplay}</td>
                    <td>
                        <button type='button' onclick='openModal(\"{$row['note']}\")'
                            style='background-color:#083690; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
                            View
                        </button>
                    </td>
                    <td>
                        <form method='POST' action='' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                        </form>";

                        if ($completionStatus != 'Approve') {
                            echo "<form method='POST' action='' style='display:inline;'>
                        <input type='hidden' name='id' value='{$row['id']}'>
                        <input type='submit' name='approve' value='Approve'
                            style='background-color:green; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
                    </form>";
                        }

                        echo "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='11'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Modal Structure -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 style="color: #0a0a0a;">NOTES:</h2>
                <br>
                <div class="body">
                    <p id="modalText">note text</p>
                </div>
            </div>
        </div>

        <script>
            // Get modal elements
            const modal = document.getElementById("myModal");
            const closeModalSpan = document.querySelector(".close");
            const modalText = document.getElementById("modalText");

            // Open modal function
            function openModal(note) {
                modalText.textContent = note;
                modal.style.display = "block";
            }

            // Close modal on 'X' click
            closeModalSpan.addEventListener("click", () => {
                modal.style.display = "none";
            });

            // Close modal if clicked outside content
            window.addEventListener("click", (event) => {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            });
        </script>
    </div>
    </div>
</body>

</html>