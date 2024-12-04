<?php
session_start();

// Admin validation
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['3'])) {
    header("Location: ../login.php");
    exit();
}

include("../../dbcon.php");

// Check database connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$editMode = false; // Flag to determine if we're editing

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = (int) $_POST['dropdown'];
    $contact = mysqli_real_escape_string($con, trim($_POST['contact'])); // The contact is now passed from the hidden input // The contact is now passed from the form
    $service_type = 9; // Orthodontic Braces ID
    $date = mysqli_real_escape_string($con, trim($_POST['date']));
    $paid = (float) $_POST['paid'];
    $bill = (float) $_POST['bill'];
    $outstanding_balance = (float) $_POST['outstanding_balance'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update logic
        $idToEdit = (int) $_POST['id'];
        $stmt = $con->prepare("UPDATE tbl_transaction_history SET name = ?, contact = ?, service_type = ?, date = ?, paid = ?, bill = ?, outstanding_balance = ? WHERE id = ?");
        $stmt->bind_param("ssisdddi", $patient_id, $contact, $service_type, $date, $paid, $bill, $outstanding_balance, $idToEdit);
    } else {
        // Insert logic
        $stmt = $con->prepare("INSERT INTO tbl_transaction_history (name, contact, service_type, date, paid, bill, outstanding_balance) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisddd", $patient_id, $contact, $service_type, $date, $paid, $bill, $outstanding_balance);
    }

    // Execute the query
    if ($stmt->execute()) {
        header("Location: transaction.php");
        exit();
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }
}

// Clear the form submitted flag after page reload
if (isset($_SESSION['form_submitted'])) {
    unset($_SESSION['form_submitted']);
}

$sql = "SELECT 
        p.id, 
        CONCAT(p.last_name, ', ', p.first_name, ' ', p.middle_name) AS full_name,
        a.contact 
    FROM tbl_patient p
    INNER JOIN tbl_transaction_history a ON p.id = a.name"; // Ensure this is correct
$result_dropdown = $con->query($sql);

// Prepare an array for dropdown options
$dropdown_options = [];
if ($result_dropdown && $result_dropdown->num_rows > 0) {
    while ($row = $result_dropdown->fetch_assoc()) {
        $dropdown_options[$row['id']] = [
            'name' => htmlspecialchars($row['full_name']),
            'contact' => htmlspecialchars($row['contact']) // Contact from tbl_appointments
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../dental.css">
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
        <form method="POST" class="s-buttons" action="../../ogout.php">
            <a href="../dental_assistant_dashboard.php"><i class="fa fa-arrow-left trash"></i></a>
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
                <li><a href="archives.php">Archives</a></a></li>
                <li><a href="transaction.php">Packages Transaction History</a></a></li>
                <li><a href="bin.php">Appointments Bin</a></li>
            </ul>
        </aside>
    </div>
    <div></div>
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

            // Capture filter values from GET parameters
            $filterName = isset($_GET['name']) ? $_GET['name'] : '';
            $filterDate = isset($_GET['date']) ? $_GET['date'] : '';

            // SQL query to count total records with filtering
            $countQuery = "SELECT COUNT(*) as total FROM tbl_transaction_history a
                       JOIN tbl_service_type s ON a.service_type = s.id
                       JOIN tbl_patient p ON a.name = p.id
                       WHERE a.service_type = '9'";

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
                  FROM tbl_transaction_history a
                  JOIN tbl_service_type s ON a.service_type = s.id
                  JOIN tbl_patient p ON a.name = p.id 
                  WHERE a.service_type ='9'";

            // Add name filter if specified
            if ($filterName) {
                $query .= " AND (p.first_name LIKE '%$filterName%' OR p.last_name LIKE '%$filterName%')";
            }

            // Add date filter if specified
            if ($filterDate) {
                $query .= " AND a.date = '$filterDate'";
            }

            $query .= " LIMIT $resultsPerPage OFFSET $startRow";  // Limit to results per page
            
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
            <br>
            <h2>Packages Transaction History</h2>
            <br>
            <!-- Table -->
            <table class="table table-bordered centered-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th style="font-size: 15px;">Rescheduled Date</th>
                        <th>Bill</th>
                        <th style="font-size: 15px;">Amount Paid</th>
                        <th style="font-size: 15px;">Outstanding Balance</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Format prices with commas
                            $paid = "₱" . number_format($row['paid'], 2);
                            $bill = "₱" . number_format($row['bill'], 2);
                            $outstanding_balance = "₱" . number_format($row['outstanding_balance'], 2);

                             // Validate modified_date and modified_time
                            $modified_date = (!empty($row['modified_date']) && $row['modified_date'] !== '0000-00-00') ? $row['modified_date'] : 'N/A';

                            // Validate date and time
                            $dateToDisplay = (!empty($row['date']) && $row['date'] !== '0000-00-00') ? $row['date'] : 'N/A';

                            echo "<tr>
                            <td style='width: 230px;'>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                            <td>{$row['contact']}</td>
                            <td style='width: 110px;'>{$row['service_name']}</td>
                            <td style='width: 110px;'>{$row['date']}</td>
                            <td style='width: 110px;'>{$modified_date}</td>
                            <td style='width: 110px;'>{$bill}</td>
                            <td style='width: 110px;'>{$paid}</td>
                            <td style='width: 110px;'>{$outstanding_balance}</td>
                            <td>
                        <button type='button' onclick='openModal(\"{$row['note']}\")'
                            style='background-color:#083690; color:white; border:none; padding:7px 9px; border-radius:10px; box-shadow: 1px 2px 5px 0px #414141; cursor:pointer;'>
                            View
                        </button>
                    </td>
            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No records found</td></tr>";
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