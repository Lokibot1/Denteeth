<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['3'])) {
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
    $conflict_query = "
        SELECT id 
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
                                     SET contact='$contact', modified_date='$modified_date', modified_time='$modified_time', modified_by = '3', service_type='$service_type' 
                                     WHERE id=$id"; // Assuming patient_id is used as foreign key in tbl_appointments

        // Execute both queries
         if (mysqli_query($con, $update_patient_query) && mysqli_query($con, $update_appointment_query)) {
            // Redirect to the same page after updating
            header("Location: pending.php");
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($con);
        }
    }
}
date_default_timezone_set('Asia/Hong_Kong');

if (isset($_POST['decline'])) {
    $id = $_POST['id'];
    $deleteQuery = "UPDATE tbl_appointments SET status = '2' WHERE id = $id";
    mysqli_query($con, $deleteQuery);

    // Redirect to refresh the page and show updated records
    header("Location: pending.php");
    
}


if (isset($_POST['restore'])) {
    $id = $_POST['id'];

    // Fetch the appointment data from tbl_archives
    $bin_query = "SELECT * FROM tbl_bin WHERE id=$id";
    $bin_result = mysqli_query($con, $bin_query);

    if ($bin_row = mysqli_fetch_assoc($bin_result)) {
        // Prepare the data to restore
        $name = mysqli_real_escape_string($con, $bin_row['name']);
        $contact = mysqli_real_escape_string($con, $bin_row['contact']);
        $date = mysqli_real_escape_string($con, $bin_row['date']);
        $time = mysqli_real_escape_string($con, $bin_row['time']);
        $modified_date = mysqli_real_escape_string($con, $bin_row['modified_date']);
        $modified_time = mysqli_real_escape_string($con, $bin_row['modified_time']);
        $service_type = mysqli_real_escape_string($con, $bin_row['service_type']);
        $status = '1'; // Setting status to '1' as active or restored
        $modified_by = '1'; // Assuming restored by admin with id '1'

        // Insert data back into tbl_appointments
        $restore_query = "INSERT INTO tbl_appointments (id, name, contact, date, time, service_type, status)
                            VALUES ('$id', '$name', '$contact', '$date', '$time', '$service_type', '$status')";

        if (mysqli_query($con, $restore_query)) {
            // Delete the record from tbl_archives
            $delete_bin_query = "DELETE FROM tbl_bin WHERE id=$id";
            if (mysqli_query($con, $delete_bin_query)) {
                // Redirect to refresh the page and show updated records
                header("Location: bin.php");
                exit();
            } else {
                echo "Error deleting record from bin: " . mysqli_error($con);
            }
        } else {
            echo "Error restoring record: " . mysqli_error($con);
        }
    } else {
        echo "No appointment found with this ID in the bin.";
    }
}

// SQL query to count total records
$countQuery = "SELECT COUNT(*) as total FROM tbl_bin WHERE status = '1'";
$countResult = mysqli_query($con, $countQuery);
$totalCount = mysqli_fetch_assoc($countResult)['total'];

// SQL query with JOIN to fetch the limited number of records
$query = "SELECT a.*, 
                s.service_type AS service_name, 
                p.first_name, p.middle_name, p.last_name
            FROM tbl_bin a
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
    <link rel="stylesheet" href="dental.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=search" />

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
            <a href="dental_assistant_dashboard.php"><i class="fa fa-arrow-left trash"></i></a>
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </nav>
    <div>
        <aside class="sidebar">
            <ul>
                <br>
                <a class="active" href="archives.php">
                    <h3>DENTAL ASSISTANT<br>ARCHIVES</h3>
                </a>
                <br>
                <br>
                <hr>
                <br>
                <li><a href="appointments_archives.php">Archives</a></a></li>
                <li><a href="transaction.php">Packages Transaction History</a></a></li>
                <li><a href="bin.php">Appointments Bin</a></li>
            </ul>
        </aside>
    </div>
    <!-- Main Content/Crud -->
    <div class="top">
        <div class="content-box">
            <?php
            // Set the number of results per page
            $resultsPerPage = 6;

            // Get the current page number from query parameters, default to 1
            $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            // Calculate the starting row for the SQL query
            $startRow = ($currentPage - 1) * $resultsPerPage;

            // Capture filter values from GET parameters
            $filterName = isset($_GET['name']) ? $_GET['name'] : '';
            $filterDate = isset($_GET['date']) ? $_GET['date'] : '';

            // SQL query to count total records with filtering
            $countQuery = "SELECT COUNT(*) as total FROM tbl_bin a
            JOIN tbl_service_type s ON a.service_type = s.id
            JOIN tbl_patient p ON a.name = p.id
            JOIN tbl_status t ON a.status = t.id
            WHERE a.status IN ('1', '2', '3', '4')";

            // Add name filter if specified
            if ($filterName) {
                $countQuery .= " AND (p.first_name LIKE '%$filterName%' OR p.last_name LIKE '%$filterName%')";
            }

            // Add date filter if specified
            if ($filterDate) {
                $countQuery .= " AND a.date = '$filterDate'";
            }

            $countResult = mysqli_query($con, $countQuery);
            $totalCount = mysqli_fetch_assoc($countResult)['total'];
            $totalPages = ceil($totalCount / $resultsPerPage); // Calculate total pages
            
            // SQL query with JOIN to fetch the filtered records with OFFSET
            $query = "SELECT a.*, 
            s.service_type AS service_name, 
            p.first_name, p.middle_name, p.last_name, 
            t.status     
        FROM tbl_bin a
        JOIN tbl_service_type s ON a.service_type = s.id
        JOIN tbl_patient p ON a.name = p.id
        JOIN tbl_status t ON a.status = t.id
        WHERE a.status IN ('1', '2', '3', '4')";

            // Add name filter if specified
            if ($filterName) {
                $query .= " AND (p.first_name LIKE '%$filterName%' OR p.last_name LIKE '%$filterName%')";
            }

            // Add date filter if specified
            if ($filterDate) {
                $query .= " AND a.date = '$filterDate'";
            }

            $query .= " LIMIT $resultsPerPage OFFSET $startRow";  // Limit to 6 rows
            
            $result = mysqli_query($con, $query);
            ?><br><br><br>
<div class="managehead">
                 <!-- Search Form Container -->
                <div class="f-search">
                    <form method="GET" action="" class="search-form">
                        <input type="text" name="name" placeholder="Search by name" value="<?php echo htmlspecialchars($filterName); ?>" />
                        <input type="date" name="date" value="<?php echo htmlspecialchars($filterDate); ?>" />
                        <button class="material-symbols-outlined" type="submit">search</button>
                    </form>
                </div>

                <!-- Pagination Navigation -->
                <div class="pagination-container">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>" class="pagination-btn">&lt;</a>
                    <?php endif; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>" class="pagination-btn">&gt;</a>
                    <?php endif; ?>
                </div>
            </div>
            <br><br>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th style="font-size: 15px;">Rescheduled Date</th>
                        <th style="font-size: 15px;">Rescheduled Time</th>
                        <th>Service</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Check if modified_date and modified_time are valid
                        $modified_date = (!empty($row['modified_date']) && $row['modified_date'] !== '0000-00-00') ? $row['modified_date'] : 'N/A';
                        $modified_time = (!empty($row['modified_time']) && $row['modified_time'] !== '00:00:00') ? date("h:i A", strtotime($row['modified_time'])) : 'N/A';

                        // Check if date and time are valid
                        $dateToDisplay = (!empty($row['date']) && $row['date'] !== '0000-00-00') ? $row['date'] : 'N/A';
                        $timeToDisplay = (!empty($row['time']) && $row['time'] !== '00:00:00') ? date("h:i A", strtotime($row['time'])) : 'N/A';

                            echo "<tr>
                        <td style='width: 230px'>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                        <td>{$row['contact']}</td>
                        <td>{$dateToDisplay}</td>
                        <td>{$timeToDisplay}</td>
                        <td>{$modified_date}</td>
                        <td>{$modified_time}</td>
                        <td>{$row['service_name']}</td>
                            </form>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>