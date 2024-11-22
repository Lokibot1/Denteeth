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
    $recommendation = mysqli_real_escape_string($con, $_POST['recommendation']);
    $price = mysqli_real_escape_string($con, $_POST['price']);


}


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
            $resultsPerPage = 20;

            // Get the current page number from query parameters, default to 1
            $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            // Calculate the starting row for the SQL query
            $startRow = ($currentPage - 1) * $resultsPerPage;

            // Capture filter values from GET parameters
            $filterName = isset($_GET['name']) ? $_GET['name'] : '';
            $filterCompletion = isset($_GET['completion']) ? $_GET['completion'] : '';
            $filterDate = isset($_GET['date']) ? $_GET['date'] : '';

            // SQL query to count total records with filtering
            $countQuery = "SELECT COUNT(*) as total 
            FROM tbl_archives a
            JOIN tbl_service_type s ON a.service_type = s.id
            JOIN tbl_patient p ON a.name = p.id
            WHERE a.completion = '2'
        ";

            // Add name filter if specified
            if ($filterName) {
                $countQuery .= " AND (p.first_name LIKE '%$filterName%' OR p.last_name LIKE '%$filterName%')";
            }

            // Add completion filter if specified
            if ($filterCompletion) {
                $countQuery .= " AND a.completion = '$filterCompletion'";
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
                   p.first_name, p.middle_name, p.last_name
            FROM tbl_archives a
            JOIN tbl_service_type s ON a.service_type = s.id
            JOIN tbl_patient p ON a.name = p.id
            WHERE a.completion = '2'
        ";

            // Add name filter if specified
            if ($filterName) {
                $query .= " AND (p.first_name LIKE '%$filterName%' OR p.last_name LIKE '%$filterName%')";
            }

            // Add completion filter if specified
            if ($filterCompletion) {
                $query .= " AND a.completion = '$filterCompletion'";
            }

            // Add date filter if specified
            if ($filterDate) {
                $query .= " AND a.date = '$filterDate'";
            }

            $query .= " LIMIT $resultsPerPage OFFSET $startRow";

            $result = mysqli_query($con, $query);
            ?><br><br><br><br>

            <!-- HTML Form for Filters -->
            <form method="GET" action="">
                <input type="text" name="name" placeholder="Search by name"
                    value="<?php echo htmlspecialchars($filterName); ?>" />

                <select name="completion">
                    <option value="">All Statuses</option>
                    <option value="1" <?php echo $filterCompletion == '1' ? 'selected' : ''; ?>>Pending</option>
                    <option value="2" <?php echo $filterCompletion == '2' ? 'selected' : ''; ?>>Declined</option>
                    <option value="3" <?php echo $filterCompletion == '3' ? 'selected' : ''; ?>>Approved</option>
                    <option value="4" <?php echo $filterCompletion == '4' ? 'selected' : ''; ?>>Finished</option>
                </select>

                <input type="date" name="date" value="<?php echo htmlspecialchars($filterDate); ?>" />

                <button class="material-symbols-outlined" type="submit">search</button>
            </form>

            <!-- Pagination -->
            <div class="pagination-container">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?>&name=<?php echo htmlspecialchars($filterName); ?>&completion=<?php echo htmlspecialchars($filterCompletion); ?>&date=<?php echo htmlspecialchars($filterDate); ?>"
                        class="pagination-btn">&lt;</a>
                <?php endif; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?>&name=<?php echo htmlspecialchars($filterName); ?>&completion=<?php echo htmlspecialchars($filterCompletion); ?>&date=<?php echo htmlspecialchars($filterDate); ?>"
                        class="pagination-btn">&gt;</a>
                <?php endif; ?>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reschedulde Date</th>
                        <th>Rescheduled Time</th>
                        <th>Type of Service</th>
                        <th>Recommendation</th>
                        <th>Price</th>
                        <th>Completion Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $modified_date = !empty($row['modified_date']) ? $row['modified_date'] : 'N/A';
                            $modified_time = !empty($row['modified_time']) ? date("h:i A", strtotime($row['modified_time'])) : 'N/A';

                            $dateToDisplay = !empty($row['date']) ? $row['date'] : 'N/A';
                            $timeToDisplay = !empty($row['time']) ? date("h:i A", strtotime($row['time'])) : 'N/A';

                            $recommendation = !empty($row['recommendation']) ? $row['recommendation'] : 'N/A';
                            $price = isset($row['price']) ? number_format($row['price'], 2) : 'N/A';
                            $completion_status = !empty($row['completion']) ? ucfirst($row['completion']) : 'N/A';

                            echo "<tr>
                            <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                            <td>{$row['contact']}</td>
                            <td>{$dateToDisplay}</td>
                            <td>{$timeToDisplay}</td>
                            <td>{$modified_date}</td>
                            <td>{$modified_time}</td>
                            <td>{$row['service_name']}</td>
                            <td>{$recommendation}</td>
                            <td>₱{$price}</td>
                            <td>{$completion_status}</td>
                        </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>