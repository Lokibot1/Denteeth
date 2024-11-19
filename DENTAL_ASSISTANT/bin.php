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

    // Update query for tbl_patient
    $update_patient_query = "UPDATE tbl_patient 
                             SET first_name='$first_name', middle_name='$middle_name', last_name='$last_name'
                             WHERE id=$id";

    // Update query for tbl_appointments
    $update_appointment_query = "UPDATE tbl_bin
                                 SET contact='$contact', modified_date='$modified_date', modified_time='$modified_time', modified_by = '3', service_type='$service_type' 
                                 WHERE id=$id";  // Assuming patient_id is used as foreign key in tbl_appointments

    // Execute both queries
    if (mysqli_query($con, $update_patient_query) && mysqli_query($con, $update_appointment_query)) {
        // Redirect to the same page after updating
        header("Location: dental_assistant_dashboard.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

date_default_timezone_set('Asia/Hong_Kong');

if (isset($_POST['delete'])) {
    // Get the ID from the form data
    $id = $_POST['id'];

    // Delete the appointment permanently from tbl_archives
    $delete_bin_query = "DELETE FROM tbl_bin WHERE id=$id";

    // Execute the delete query
    if (mysqli_query($con, $delete_bin_query)) {
        // Redirect to the same page after deleting
        header("Location: bin.php");
        exit();
    } else {
        echo "Error permanently deleting appointment record from bin: " . mysqli_error($con);
    }
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
    <link rel="stylesheet" href="dental_assistant.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=search" />

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
        <form method="POST" action="../logout.php">
            <button type="submit" class="logout-button">Logout</button>
        </form>
        <a href="dental_assistant_dashboard.php"><i class="fa fa-arrow-left trash"></i></a>
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
                <li><a href="appointments_archives.php">Appointments Archives</a></a></li>
                <li><a href="bin.php">Appointments Bin</a></li>
            </ul>
        </aside>
    </div>
    <!-- Main Content/Crud -->
    <div class="top">
        <div class="content-box">
            <?php
            // Set the number of results per page
            $resultsPerPage = 10;

            // Get the current page number from query parameters, default to 1
            $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            // Calculate the starting row for the SQL query
            $startRow = ($currentPage - 1) * $resultsPerPage;

            // Capture filter values from GET parameters
            $filterName = isset($_GET['name']) ? $_GET['name'] : '';
            $filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
            $filterDate = isset($_GET['date']) ? $_GET['date'] : '';

            // SQL query to count total records with filtering
            $countQuery = "SELECT COUNT(*) as total FROM tbl_bin a
               JOIN tbl_service_type s ON a.service_type = s.id
               JOIN tbl_patient p ON a.id = p.id
               JOIN tbl_status t ON a.status = t.id
               WHERE a.status IN ('1', '2', '3', '4')";

            // Add name filter if specified
            if ($filterName) {
                $countQuery .= " AND (p.first_name LIKE '%$filterName%' OR p.last_name LIKE '%$filterName%')";
            }

            // Add status filter if specified
            if ($filterStatus) {
                $countQuery .= " AND a.status = '$filterStatus'";
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
          JOIN tbl_patient p ON a.id = p.id
          JOIN tbl_status t ON a.status = t.id
          WHERE a.status IN ('1', '2', '3', '4')";

            // Add name filter if specified
            if ($filterName) {
                $query .= " AND (p.first_name LIKE '%$filterName%' OR p.last_name LIKE '%$filterName%')";
            }

            // Add status filter if specified
            if ($filterStatus) {
                $query .= " AND a.status = '$filterStatus'";
            }

            // Add date filter if specified
            if ($filterDate) {
                $query .= " AND a.date = '$filterDate'";
            }

            $query .= " LIMIT $resultsPerPage OFFSET $startRow";  // Limit to 15 rows
            
            $result = mysqli_query($con, $query);
            ?><br><br><br><br>

            <!-- HTML Form for Filters -->
            <form method="GET" action="" class="search-form">
                <input type="text" name="name" placeholder="Search by name"
                    value="<?php echo htmlspecialchars($filterName); ?>" />

                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="1" <?php echo $filterStatus == '1' ? 'selected' : ''; ?>>Pending</option>
                    <option value="2" <?php echo $filterStatus == '2' ? 'selected' : ''; ?>>Declined</option>
                    <option value="3" <?php echo $filterStatus == '3' ? 'selected' : ''; ?>>Approved</option>
                    <option value="4" <?php echo $filterStatus == '4' ? 'selected' : ''; ?>>Finished</option>
                </select>

                <input type="date" name="date" value="<?php echo htmlspecialchars($filterDate); ?>" />

                <span class="material-symbols-outlined" type = "submit">search</span>
            </form>
            <!-- Pagination -->
            <div class="pagination-container">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?>&name=<?php echo htmlspecialchars($filterName); ?>&status=<?php echo htmlspecialchars($filterStatus); ?>"
                        class="pagination-btn">
                        < </a>
                        <?php endif; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?>&name=<?php echo htmlspecialchars($filterName); ?>&status=<?php echo htmlspecialchars($filterStatus); ?>"
                                class="pagination-btn"> > </a>
                        <?php endif; ?>
            </div>
            <br><br><br>
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
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Check if modified_date and modified_time are empty
                        $modified_date = !'' && !empty($row['modified_date']) ? $row['modified_date'] : 'N/A';
                        $modified_time = !'' && !empty($row['modified_time']) ? date("h:i A", strtotime($row['modified_time'])) : 'N/A';

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
                    </form>";
                        if ($row['status'] != 'Restore') {
                            echo "<form method='POST' action='' style='display:inline;'>
                        <input type='hidden' name='id' value='{$row['id']}'>
                        <input type='submit' name='restore' value='Restore' 
                        style='background-color:green; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
                    </form>";
                        }
                        if ($row['status'] != 'Delete') {
                            echo "<form method='POST' action='' style='display:inline;'>
                    <input type='hidden' name='id' value='{$row['id']}'>
                    <input type='submit' name='delete' value='Delete' 
                    style='background-color: rgb(196, 0, 0); color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
                </form>";
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
    </div>
</body>

</html>