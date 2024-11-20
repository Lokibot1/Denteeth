<?php
session_start();



// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['3'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
include("../dbcon.php");



$editMode = false; // Flag to determine if we're editing
$idToEdit = null; // Variable to hold the ID of the record to edit




if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's an update or insert based on whether an ID is present in POST data
    if (isset($_POST['id'])) {
        // Update existing transaction
        $idToEdit = (int) $_POST['id'];
        $stmt = $con->prepare("UPDATE tbl_archives SET name = ?, contact = ?, service_type = ?, date = ?, time = ?, bill = ?, change_amount = ?, outstanding_balance = ? WHERE id = ?");
        $stmt->bind_param("issssdddi", $patient_name, $contact, $service_type, $date, $time, $bill, $change_amount, $outstanding_balance, $idToEdit);

        // Set the flag to true
        $editMode = true;
    } else {
        // Insert new transaction
        $stmt = $con->prepare("INSERT INTO tbl_archives (name, contact, service_type, date, time, bill, change_amount, outstanding_balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssddd", $patient_name, $contact, $service_type, $date, $time, $bill, $change_amount, $outstanding_balance);
    }

    // Get and sanitize values from POST
    $patient_name = mysqli_real_escape_string($con, $_POST['dropdown']); // Set name to the selected ID
    $contact = mysqli_real_escape_string($con, trim($_POST['contact']));
    $service_type = mysqli_real_escape_string($con, trim($_POST['service_type']));
    $date = mysqli_real_escape_string($con, trim($_POST['date']));
    $time = mysqli_real_escape_string($con, trim($_POST['time']));
    $bill = (float) $_POST['bill'];
    $change_amount = (float) $_POST['change_amount'];
    $outstanding_balance = (float) $_POST['outstanding_balance'];

    // Execute the query
    if ($stmt->execute()) {
        // Clear form submitted flag after successful operation
        unset($_SESSION['form_submitted']);
        header("Location: " . $_SERVER['REQUEST_URI']); // Redirect to avoid form resubmission
        exit();
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    // Close connections
    $stmt->close();
}

// Clear the form submitted flag after page reload
if (isset($_SESSION['form_submitted'])) {
    unset($_SESSION['form_submitted']);
}

$sql = "SELECT id, last_name, first_name, middle_name FROM tbl_patient"; // Update with your actual table and field names
$result_dropdown = $con->query($sql);

// Prepare an array for dropdown options
$dropdown_options = [];
if ($result_dropdown && $result_dropdown->num_rows > 0) {
    while ($row = $result_dropdown->fetch_assoc()) {
        $full_name = htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name']);
        $dropdown_options[$row['id']] = $full_name; // Using patient ID as the key
    }
} else {
    echo "<p>No patients found for the dropdown.</p>";
}

$result = mysqli_query($con, "SELECT * FROM tbl_archives");

// Update functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Retrieve updated values from the form
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $service_type = mysqli_real_escape_string($conn, $_POST['service_type']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);
    $bill = mysqli_real_escape_string($conn, $_POST['bill']);
    $change_amount = mysqli_real_escape_string($conn, $_POST['change_amount']);
    $outstanding_balance = mysqli_real_escape_string($conn, $_POST['outstanding_balance']);
    $

        // Update query
        $updateQuery = "UPDATE transactions SET 
            contact = '$contact',
            service_name = '$service_type',
            date = '$date',
            time = '$time',
            bill = '$bill',
            change_amount = '$change_amount',
            outstanding_balance = '$outstanding_balance'
            WHERE id = '$id'";

    // Execute the query
    if (mysqli_query($conn, $updateQuery)) {
        echo "<script>alert('Record updated successfully'); window.location.href='archives.php';</script>";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..900&display=swap" rel="stylesheet">
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
        <a href="archives.php"><i class="fas fa-trash trash"></i></a>
    </nav>
    <aside class="sidebar">
        <ul>
            <br>
            <a class="active" href="dental_assistant_dashboard.php">
                <h3>DENTAL ASSISTANT<br>DASHBOARD</h3>
            </a>
            <br>
            <br>
            <hr>
            <br>
            <li><a href="pending.php">Pending Appointments</a></a></li>
            <li><a href="appointments.php">Approved Appointments</a></li>
            <li><a href="declined.php">Declined Appointment</a></li>
            <li><a href="billing.php">Billing Approval</a></li>
        </ul>
    </aside>

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
                        <th>Modified_Date</th>
                        <th>Modified_Time</th>
                        <th>Type Of Service</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Action</th>
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

                            $priceToDisplay = isset($row['price']) ? number_format($row['price']) : 'N/A';
                            // Translate ENUM values for completion
                            $completionStatus = 'Unknown'; // Default value
                            if (isset($row['completion'])) {
                                switch ($row['completion']) {
                                    case '1':
                                        $completionStatus = 'Pending';
                                        break;
                                    case '2':
                                        $completionStatus = 'Completed';
                                        break;
                                    case '3':
                                        $completionStatus = 'Incomplete Payment';
                                        break;
                                }
                            }

                            echo "<tr>
            <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
            <td>{$row['contact']}</td>
            <td>{$dateToDisplay}</td>
            <td>{$timeToDisplay}</td>
            <td>{$modified_date}</td>
            <td>{$modified_time}</td>
            <td>{$row['service_name']}</td> 
            <td>{$completionStatus}</td>
            <td>{$priceToDisplay}</td>
            <td>    
        <button type='button' onclick='openModal({$row['id']}, \"{$row['first_name']}\", \"{$row['middle_name']}\", \"{$row['last_name']}\", \"{$row['contact']}\", \"{$dateToDisplay}\", \"{$timeToDisplay}\", \"{$row['service_name']}\")' 
                        style='background-color:#083690; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>Update</button>
                        <form method='POST' action='' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                        </form>";
                            if ($row['status'] != 'Approval') {
                                echo "<form method='POST' action='' style='display:inline;'>
                                <input type='hidden' name='id' value='{$row['id']}'>
                                <input type='submit' name='approval' value='Approval' 
                                style='background-color:green; color:white; border:none;  padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
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
            <br><br>

            <!-- Modal for adding new transactions -->
            <div id="transactionModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2><?php echo $editMode ? 'Edit Transaction' : 'Add Transaction'; ?></h2>
                    <form method="POST" action="">
                        <label for="dropdown">Choose an option:</label>
                        <select name="dropdown" required>
                            <option value="">Select a patient</option>
                            <?php
                            // Filter out duplicates by name
                            $unique_options = [];
                            foreach ($dropdown_options as $id => $name) {
                                if (!in_array($name, $unique_options)) {
                                    $unique_options[$id] = $name;
                                }
                            }

                            // Generate dropdown options with unique names
                            foreach ($unique_options as $id => $name): ?>
                                <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <br>
                        <label for="modal-contact">Contact:</label>
                        <input type="text" name="contact" id="modal-contact" placeholder="Enter your contact number"
                            maxlength="11" required pattern="\d{11}" title="Please enter exactly 11 digits"><br>
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
                        <label for="date">Date:</label>
                        <input type="date" name="date" id="modal-date" required>
                        <br>
                        <p>
                            <label for="time">Time: <br> CLINIC HOURS 9:00 AM TO 6:00 PM</label>
                            <input type="time" name="time" id="modal-time" min="09:00" max="18:00" required>

                        </p>
                        <div class="bill-fields">
                            <label for="modal-bill">Bill:</label>
                            <label for="modal-change">Change Amount:</label>
                            <label for="modal-balance">Outstanding Balance:</label>
                        </div>
                        <div class="bill-inputs">
                            <input type="number" step="0.01" name="bill" id="modal-bill" required>
                            <input type="number" step="0.01" name="change_amount" id="modal-change" required>
                            <input type="number" step="0.01" name="outstanding_balance" id="modal-balance" required>
                        </div>

                        <button type="submit"><?php echo $editMode ? 'Update' : 'Add'; ?></button>
                    </form>
                </div>
            </div>

            <script>
                // Modal functions
                const modal = document.getElementById("transactionModal");
                const btn = document.getElementById("openModalBtn");

                btn.onclick = function () {
                    openModal(); // Call openModal function when the button is clicked
                }

                function openModal(id, firstName, middleName, lastName, contact, date, time, serviceType) {
                    document.getElementById('transactionModal').style.display = "block";
                    if (id) {
                        // Populate the form for editing
                        document.querySelector('input[name="id"]').value = id;
                        document.querySelector('input[name="contact"]').value = contact;
                        document.querySelector('input[name="service_type"]').value = serviceType;
                        document.querySelector('input[name="date"]').value = date;
                        document.querySelector('input[name="time"]').value = time;
                    } else {
                        // Clear the form for adding
                        document.querySelector('input[name="id"]').value = '';
                        document.querySelector('input[name="contact"]').value = '';
                        document.querySelector('input[name="service_type"]').value = '';
                        document.querySelector('input[name="date"]').value = '';
                        document.querySelector('input[name="time"]').value = '';
                    }
                }

                function closeModal() {
                    document.getElementById('transactionModal').style.display = "none";
                }
            </script>

            <!-- Modal2 -->
            <div id="modal2" class="modal2">
                <div class="modal2-content">
                    <span class="close" onclick="closeModal2()">&times;</span>
                    <h2>Edit Transaction</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="modal2-id">
                        <label for="dropdown">Choose an option:</label>
                        <select name="dropdown" required>
                            <option value="">Select a patient</option>
                            <?php foreach ($dropdown_options as $id => $name): ?>
                                <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <br>
                        <label for="contact">Contact:</label>
                        <input type="text" name="contact" id="modal2-contact" required>

                        <label for="service_type">Type Of Service:</label>
                        <select name="service_type" id="modal2-service_type" required>
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
                        <label for="date">Date:</label>
                        <input type="date" name="date" id="modal2-date" required>

                        <label for="time">Time:</label>
                        <input type="time" name="time" id="modal2-time" required>

                        <label for="bill">Bill:</label>
                        <input type="number" name="bill" id="modal2-bill" step="0.01" required>

                        <label for="change_amount">Change Amount:</label>
                        <input type="number" name="change_amount" id="modal2-change_amount" step="0.01" required>

                        <label for="outstanding_balance">Outstanding Balance:</label>
                        <input type="number" name="outstanding_balance" id="modal2-outstanding_balance" step="0.01"
                            required>

                        <!-- Update button -->
                        <button type="submit" name="update"
                            style="background-color:green; color:white; padding:5px 10px; border:none; border-radius:5px; cursor:pointer;">Update</button>
                    </form>
                </div>
            </div>

            <script>
                // Function to open modal2 and populate fields
                function openModal2(id, contact, service_type, date, time, bill, change_amount, outstanding_balance) {
                    document.getElementById("modal2-id").value = id;
                    document.getElementById("modal2-contact").value = contact;
                    document.getElementById("modal2-service_type").value = service_type;
                    document.getElementById("modal2-date").value = date;
                    document.getElementById("modal2-time").value = time;
                    document.getElementById("modal2-bill").value = bill;
                    document.getElementById("modal2-change_amount").value = change_amount;
                    document.getElementById("modal2-outstanding_balance").value = outstanding_balance;

                    document.getElementById("modal2").style.display = "block";
                }

                // Function to close modal2
                function closeModal2() {
                    document.getElementById("modal2").style.display = "none";
                }
            </script>

        </div>
</body>

</html>