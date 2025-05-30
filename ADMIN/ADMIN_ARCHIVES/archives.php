<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['1'])) {
    header("Location: ../login.php");
    exit();
}

include("../../dbcon.php");

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
    <link rel="stylesheet" href="../ad.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=search" />
    <title>Admin Dashboard</title>
</head>

<body>
    <!-- Navigation/Sidebar -->
    <nav>
        <a >
            <div class="logo">
                <h1><span>EHM</span> Dental Clinic</h1>
            </div>
        </a>
        <form method="POST" class="s-buttons" action="../../logout.php">
            <a href="../admin_dashboard.php"><i class="fa fa-arrow-left trash"></i></a>
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </nav>
    <div>
        <aside class="sidebar">
            <ul>
                <br>
                <a class="active" href="archives.php">
                    <h3>ADMIN<br>ARCHIVES</h3>
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
            $resultsPerPage = 5;

            // Get the current page number from query parameters, default to 1
            $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            // Calculate the starting row for the SQL query
            $startRow = ($currentPage - 1) * $resultsPerPage;

            // Capture filter values from GET parameters (sanitize inputs)
            $filterName = isset($_GET['name']) ? mysqli_real_escape_string($con, $_GET['name']) : '';
            $filterDate = isset($_GET['date']) ? mysqli_real_escape_string($con, $_GET['date']) : '';

            // SQL query to count total records with filtering
            $countQuery = "SELECT COUNT(*) AS total 
                FROM tbl_archives a
                JOIN tbl_service_type s ON a.service_type = s.id
                JOIN tbl_patient p ON a.name = p.id
                JOIN tbl_status t ON a.completion = t.id
                WHERE a.completion IN ('1', '2', '3')";

            // Add filters
            if ($filterName) {
                $countQuery .= " AND (p.first_name LIKE '%$filterName%' OR p.last_name LIKE '%$filterName%')";
            }
            if ($filterDate) {
                $countQuery .= " AND a.date = '$filterDate'";
            }

            $countResult = mysqli_query($con, $countQuery);
            $totalCount = mysqli_fetch_assoc($countResult)['total'];
            $totalPages = ceil($totalCount / $resultsPerPage);

            // SQL query to fetch the filtered records with OFFSET
            $query = "SELECT a.*, 
                s.service_type AS service_name, 
                p.first_name, p.middle_name, p.last_name, 
                t.status     
            FROM tbl_archives a
            JOIN tbl_service_type s ON a.service_type = s.id
            JOIN tbl_patient p ON a.name = p.id
            JOIN tbl_status t ON a.completion = t.id
            WHERE a.completion IN ('1', '2', '3')";

            // Add filters
            if ($filterName) {
                $query .= " AND (p.first_name LIKE '%$filterName%' OR p.last_name LIKE '%$filterName%')";
            }
            if ($filterDate) {
                $query .= " AND a.date = '$filterDate'";
            }

            // Add ordering and pagination
            $query .= "ORDER BY CASE 
                WHEN a.modified_date IS NOT NULL THEN a.modified_date
                ELSE a.date
                END DESC
                LIMIT $resultsPerPage OFFSET $startRow";

            $result = mysqli_query($con, $query);
            ?>

            <br><br><br>
            <div class="managehead">
                <!-- Search Form Container -->
                <div class="f-search">
                    <form method="GET" action="" class="search-form">
                        <input type="text" name="name" placeholder="Search by name"
                            value="<?php echo htmlspecialchars($filterName); ?>" />
                        <input type="date" name="date" value="<?php echo htmlspecialchars($filterDate); ?>" />
                        <button class="material-symbols-outlined" type="submit">search</button>
                    </form>
                </div>

                <!-- Pagination Navigation -->
                <div class="pagination-container">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>&name=<?php echo urlencode($filterName); ?>&date=<?php echo urlencode($filterDate); ?>"
                            class="pagination-btn">&lt;</a>
                    <?php endif; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>&name=<?php echo urlencode($filterName); ?>&date=<?php echo urlencode($filterDate); ?>"
                            class="pagination-btn">&gt;</a>
                    <?php endif; ?>
                </div>
            </div>
            <br><br>

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
                        <th>Service</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Handle modified_date and modified_time
                            $modified_date = !empty($row['modified_date']) ? $row['modified_date'] : $row['date'];
                            $modified_time = !empty($row['modified_time']) ? $row['modified_time'] : $row['time'];

                            // Format time
                            $timeToDisplayFormatted = date("h:i A", strtotime($row['time']));
                            $timeToDisplayFormattedModified = date("h:i A", strtotime($modified_time));

                            echo "<tr>
                    <td style='width: 200px'>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                    <td>{$row['contact']}</td>
                    <td style='width: 90px'>{$row['date']}</td>
                    <td style='width: 90px'>{$timeToDisplayFormatted}</td>
                    <td>{$modified_date}</td>
                    <td>{$timeToDisplayFormattedModified}</td>
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
    </div>
</body>

</html>