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
    $note = mysqli_real_escape_string($con, $_POST['note']);
    $price = mysqli_real_escape_string($con, $_POST['price']);


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
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=search" />
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
            <a href="admin_dashboard.php"><i class="fa fa-arrow-left trash"></i></a>
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
            $resultsPerPage = 9;

            // Get the current page number from query parameters, default to 1
            $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            // Calculate the starting row for the SQL query
            $startRow = ($currentPage - 1) * $resultsPerPage;

            // Capture filter values from GET parameters
            $filterName = isset($_GET['name']) ? $_GET['name'] : '';
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

            // Add date filter if specified
            if ($filterDate) {
                $query .= " AND a.date = '$filterDate'";
            }

            $query .= " LIMIT $resultsPerPage OFFSET $startRow";

            $result = mysqli_query($con, $query);
            ?><br><br><br>
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
                        <th>Modified Date</th>
                        <th>Modified Time</th>
                        <th>Type of Service</th>
                        <th>Price</th>
                        <th>Completion Status</th>
                        <th>Note</th>
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

                            $note = !empty($row['note']) ? $row['note'] : 'N/A';
                            $price = isset($row['price']) ? number_format($row['price'], 2) : 'N/A';

                            // Check completion status and replace 2 with 'Completed'
                            $completion_status = ($row['completion'] == 2) ? 'Completed' : (!empty($row['completion']) ? ucfirst($row['completion']) : 'N/A');

                            echo "<tr>
                <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                <td>{$row['contact']}</td>
                <td>{$dateToDisplay}</td>
                <td>{$timeToDisplay}</td>
                <td>{$modified_date}</td>
                <td>{$modified_time}</td>
                <td>{$row['service_name']}</td>
                <td>₱{$price}</td>
                <td>{$completion_status}</td>
                <td>
                        <button type='button' onclick='openModal(\"{$row['note']}\")'
                            style='background-color:#083690; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
                            View
                        </button>
                    </td>
            </tr>";
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