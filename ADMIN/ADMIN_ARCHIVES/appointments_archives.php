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
        <a href="../HOME_PAGE/Home_page.php">
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

    <!-- Main Content -->
    <div class="top">
        <div class="content-box">
            <?php
            // Set the number of results per page
            $resultsPerPage = 9;

            // Get the current page number from query parameters, default to 1
            $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            // Calculate the starting row for the SQL query
            $startRow = ($currentPage - 1) * $resultsPerPage;

            // Capture and sanitize filter values from GET parameters
            $filterName = isset($_GET['name']) ? mysqli_real_escape_string($con, $_GET['name']) : '';
            $filterDate = isset($_GET['date']) ? mysqli_real_escape_string($con, $_GET['date']) : '';

            // SQL query to count total records with filtering
            $countQuery = "SELECT COUNT(*) as total 
                           FROM tbl_archives a
                           JOIN tbl_service_type s ON a.service_type = s.id
                           JOIN tbl_patient p ON a.name = p.id
                           WHERE a.completion = '2'";

            if ($filterName) {
                $countQuery .= " AND (p.first_name LIKE '%$filterName%' OR p.last_name LIKE '%$filterName%')";
            }
            if ($filterDate) {
                $countQuery .= " AND a.date = '$filterDate'";
            }

            $countResult = mysqli_query($con, $countQuery);
            $totalCount = mysqli_fetch_assoc($countResult)['total'];
            $totalPages = ceil($totalCount / $resultsPerPage);

            // SQL query to fetch filtered and paginated records
            $query = "SELECT a.*, 
                             s.service_type AS service_name, 
                             p.first_name, p.middle_name, p.last_name
                      FROM tbl_archives a
                      JOIN tbl_service_type s ON a.service_type = s.id
                      JOIN tbl_patient p ON a.name = p.id
                      WHERE a.completion = '2'";

            if ($filterName) {
                $query .= " AND (p.first_name LIKE '%$filterName%' OR p.last_name LIKE '%$filterName%')";
            }
            if ($filterDate) {
                $query .= " AND a.date = '$filterDate'";
            }

            $query .= " ORDER BY 
                        CASE 
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
                        <th>Price</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if there are records in the result set
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Set default values for modified date and time
                            $modified_date = !empty($row['modified_date']) ? $row['modified_date'] : 'N/A';
                            $modified_time = !empty($row['modified_time']) ? date("h:i A", strtotime($row['modified_time'])) : 'N/A';

                            // Set default values for original date and time
                            $dateToDisplay = !empty($row['date']) ? $row['date'] : 'N/A';
                            $timeToDisplay = !empty($row['time']) ? date("h:i A", strtotime($row['time'])) : 'N/A';

                            // Format additional details
                            $note = !empty($row['note']) ? $row['note'] : 'N/A';
                            $price = isset($row['price']) ? number_format($row['price'], 2) : 'N/A';

                            // Generate the row for the table
                            echo "<tr>
                    <td style='width: 200px'>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                    <td>{$row['contact']}</td>
                    <td style='width: 90px'>{$dateToDisplay}</td>
                    <td style='width: 90px'>{$timeToDisplay}</td>
                    <td>{$modified_date}</td>
                    <td>{$modified_time}</td>
                    <td>{$row['service_name']}</td>
                    <td>₱{$price}</td>
                    <td>
                        <button type='button' onclick='openModal(\"{$row['note']}\")'
                            style='background-color:#083690; color:white; border:none; padding:10px; border-radius:10px; box-shadow: 1px 2px 5px 0px #414141; cursor:pointer;'>
                            View
                        </button>
                    </td>
                </tr>";
                        }
                    } else {
                        // Display a message if no records are found
                        echo "<tr><td colspan='9'>No records found</td></tr>";
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
                document.addEventListener("DOMContentLoaded", function () {
                // Get the current URL path
                const currentPath = window.location.pathname.split("/").pop();

                // Select all sidebar links
                const sidebarLinks = document.querySelectorAll(".sidebar a");

                // Loop through each link to find a match
                sidebarLinks.forEach(link => {
                    if (link.getAttribute("href") === currentPath) {
                        // Remove the active class from all links first
                        sidebarLinks.forEach(l => l.classList.remove("active"));
                        // Add the active class to the matching link
                        link.classList.add("active");

                        // If it's inside a <li>, add a class to <li> as well
                        if (link.parentElement.tagName === "LI") {
                            link.parentElement.classList.add("active");
                        }
                    }
                });
            });
            </script>
        </div>
    </div>
</body>

</html>