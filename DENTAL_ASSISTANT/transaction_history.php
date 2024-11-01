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
        $stmt = $con->prepare("UPDATE tbl_transaction_history SET name = ?, contact = ?, service_type = ?, date = ?, time = ?, bill = ?, change_amount = ?, outstanding_balance = ? WHERE id = ?");
        $stmt->bind_param("issssdddi", $patient_name, $contact, $service_type, $date, $time, $bill, $change_amount, $outstanding_balance, $idToEdit);

        // Set the flag to true
        $editMode = true;
    } else {
        // Insert new transaction
        $stmt = $con->prepare("INSERT INTO tbl_transaction_history (name, contact, service_type, date, time, bill, change_amount, outstanding_balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dental_assistant_dashboard.css">
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
    </nav>

    <aside class="sidebar">
        <ul>
            <br>
            <a href="dental_assistant_dashboard.php">
                <h3>DENTAL ASSISTANT<br>DASHBOARD</h3>
            </a>
            <br>
            <br>
            <hr>
            <br>
            <li><a href="pending.php">Pending Appointments</a></li>
            <li><a href="day.php">Appointment for the day</a></li>
            <li><a href="week.php">Appointment for the week</a></li>
            <li><a href="declined.php">Declined Appointment</a></li>
            <li><a class="active" href="transaction_history.php">Transaction History</a></li>
        </ul>
    </aside>

    <!-- Main Content/CRUD -->
    <div class="top">
        <div class="content-box">
            <div class="round-box">
                <p>APPOINTMENT TODAY:</p>
                <?php
                date_default_timezone_set('Asia/Hong_Kong');
                $today = date('Y-m-d');

                $sql_today = "SELECT COUNT(*) as total_appointments_today 
                              FROM tbl_appointments 
                              WHERE (DATE(date) = '$today' OR DATE(modified_date) = '$today') AND status = '1'";
                $result_today = mysqli_query($con, $sql_today);

                if ($result_today) {
                    $row_today = mysqli_fetch_assoc($result_today);
                    $appointments_today = $row_today['total_appointments_today'];
                    echo $appointments_today ? $appointments_today : 'No data available';
                } else {
                    echo "Error: " . mysqli_error($con);
                }
                ?>
            </div>
            <div class="round-box">
                <p>PENDING APPOINTMENTS:</p>
                <?php
                $sql_pending = "SELECT COUNT(*) as total_pending_appointments FROM tbl_appointments WHERE status = '1'";
                $result_pending = mysqli_query($con, $sql_pending);

                if ($result_pending) {
                    $row_pending = mysqli_fetch_assoc($result_pending);
                    $pending_appointments = $row_pending['total_pending_appointments'];
                    echo $pending_appointments ? $pending_appointments : 'No data available';
                } else {
                    echo "Error: " . mysqli_error($con);
                }
                ?>
            </div>
            <div class="round-box">
                <p>APPOINTMENT FOR THE WEEK:</p>
                <?php
                $start_of_week = date('Y-m-d', strtotime('monday this week'));
                $end_of_week = date('Y-m-d', strtotime('sunday this week'));

                $sql_week = "SELECT COUNT(*) as total_appointments_week 
                             FROM tbl_appointments 
                             WHERE (DATE(date) BETWEEN '$start_of_week' AND '$end_of_week' 
                             OR DATE(modified_date) BETWEEN '$start_of_week' AND '$end_of_week') 
                             AND status = '1'";
                $result_week = mysqli_query($con, $sql_week);

                if ($result_week) {
                    $row_week = mysqli_fetch_assoc($result_week);
                    $appointments_for_week = $row_week['total_appointments_week'];
                    echo $appointments_for_week ? $appointments_for_week : 'No data available';
                } else {
                    echo "Error: " . mysqli_error($con);
                }
                ?>
            </div>
            <div class="round-box">
                <p>DECLINED APPOINTMENTS:</p>
                <?php
                $sql_finished = "SELECT COUNT(*) as total_finished_appointments FROM tbl_appointments WHERE status = '2'";
                $result_finished = mysqli_query($con, $sql_finished);

                if ($result_finished) {
                    $row_finished = mysqli_fetch_assoc($result_finished);
                    $finished_appointments = $row_finished['total_finished_appointments'];
                    echo $finished_appointments ? $finished_appointments : 'No data available';
                } else {
                    echo "Error: " . mysqli_error($con);
                }
                ?>
            </div>
        </div>

        <h2>Transaction History</h2>
        <button id="openModalBtn" class="pagination-btn">Add New Transaction</button>

        <?php
        // Set the number of results per page
        $resultsPerPage = 20;

        // Get the current page number from query parameters, default to 1
        $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        // Calculate the starting row for the SQL query
        $startRow = ($currentPage - 1) * $resultsPerPage;

        // SQL query to count total records
        $countQuery = "SELECT COUNT(*) as total FROM tbl_transaction_history";
        $countResult = mysqli_query($con, $countQuery);
        $totalCount = mysqli_fetch_assoc($countResult)['total'];
        $totalPages = ceil($totalCount / $resultsPerPage); // Calculate total pages
        
        // SQL query with JOIN to fetch the limited number of records with OFFSET
        $query = "SELECT a.*, 
            s.service_type AS service_name, 
            p.first_name, p.middle_name, p.last_name  
          FROM tbl_transaction_history a
          JOIN tbl_service_type s ON a.service_type = s.id
          JOIN tbl_patient p ON a.name = p.id 
          LIMIT $resultsPerPage OFFSET $startRow";  // Limit to 15 rows
        
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
    </div>
    <!-- Table -->
    <table class="table table-bordered centered-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Type Of Service</th>
                <th>Date</th>
                <th>Time</th>
                <th>Bill</th>
                <th>Change Amount</th>
                <th>Outstanding Balance</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Check if modified_date and modified_time are empty
                    $dateToDisplay = !empty($row['date']) ? $row['date'] : $row['date'];
                    $timeToDisplay = !empty($row['time']) ? $row['time'] : $row['time'];

                    // Format time to HH:MM AM/PM
                    $timeToDisplayFormatted = date("h:i A", strtotime($timeToDisplay));

                    echo "<tr>
                        <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
                        <td>{$row['contact']}</td>
                        <td>{$row['service_name']}</td>
                        <td>{$dateToDisplay}</td>
                        <td>{$timeToDisplayFormatted}</td>
                        <td>{$row['bill']}</td>
                        <td>{$row['change_amount']}</td>
                        <td>{$row['outstanding_balance']}</td>
                        <td>
                               <button type='button' onclick='openModal({$row['id']}, \"{$row['first_name']}\", \"{$row['middle_name']}\", \"{$row['last_name']}\", \"{$row['contact']}\", \"{$row['date']}\", \"{$timeToDisplayFormatted}\", \"{$row['service_name']}\")' 
                                style='background-color:#083690; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>Update</button>
                                <form method='POST' action='' style='display:inline;'>
                                    <input type='hidden' name='id' value='{$row['id']}'>
                                    <input type='submit' name='delete' value='delete' 
                                    style='background-color: rgb(196, 0, 0); color:white; border:none;  padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
                                </form>
                            </td>
            </tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Modal for adding new transactions -->
    <div id="transactionModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2><?php echo $editMode ? 'Edit Transaction' : 'Add Transaction'; ?></h2>
            <form method="POST" action="">
                <label for="dropdown">Choose an option:</label>
                <select name="dropdown" required>
                    <option value="">Select a patient</option>
                    <?php foreach ($dropdown_options as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
                <br>
                <label for="modal-contact">Contact:</label>
                <input type="text" name="contact" id="modal-contact" required>
                <br>
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
                    <label for="time">Time:</label>
                    <input type="time" name="time" id="modal-time" min="09:00" max="18:00" required>
                    CLINIC HOURS 9:00 AM TO 6:00 PM
                </p>
                <label for="modal-bill">Bill:</label>
                <input type="number" step="0.01" name="bill" id="modal-bill" required>
                <br>
                <label for="modal-change">Change Amount:</label>
                <input type="number" step="0.01" name="change_amount" id="modal-change" required>
                <br>
                <label for="modal-balance">Outstanding Balance:</label>
                <input type="number" step="0.01" name="outstanding_balance" id="modal-balance" required>
                <br>
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

    </div>
</body>

</html>