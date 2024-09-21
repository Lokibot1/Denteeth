<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../login.php");
    exit();
}

// Database connection
include("../dbcon.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_SESSION['form_submitted'])) {
    // Prepare and bind
    $stmt = $con->prepare("INSERT INTO transaction_history (patient_name, contact_no, type_of_service, date_of_service, bill, change_amount, outstanding_balance) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssddd", $patient_name, $contact_no, $type_of_service, $date_of_service, $bill, $change_amount, $outstanding_balance);

    // Get values from POST
    $patient_name = $_POST['patient_name'];
    $contact_no = $_POST['contact_no'];
    $type_of_service = $_POST['type_of_service'];
    $date_of_service = $_POST['date_of_service'];
    $bill = $_POST['bill'];
    $change_amount = $_POST['change_amount'];
    $outstanding_balance = $_POST['outstanding_balance'];

    // Execute the query
    if ($stmt->execute()) {
        // Set a flag in the session to prevent form resubmission
        $_SESSION['form_submitted'] = true;

        // Redirect to the same page to clear POST data
        header("Location: " . $_SERVER['REQUEST_URI']);
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="transaction_history.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <title>Doctor Dashboard</title>
</head>

<body>
    <!-- Navigation/Sidebar -->
    <nav>
        <div class="logo-container">
            <label class="logo">Denteeth</label>
            <form method="POST" action="../logout.php">
                <button type="submit" class="logout-button">Logout</button>
            </form>
        </div>
        <div class="w3-sidebar w3-light-grey w3-bar-block custom-sidebar">
            <a href="doctor_dashboard.php">
                <h3 class="w3-bar-item">DOCTOR<br>DASHBOARD</h3>
            </a>
            <a href="day.php" class="w3-bar-item w3-button">Appointment for the day</a>
            <a href="week.php" class="w3-bar-item w3-button">Appointment for the week</a>
            <a href="finished.php" class="w3-bar-item w3-button">Finished Appointments</a>
            <a href="services.php" class="w3-bar-item w3-button">Services</a>
            <a href="transaction_history.php" class="w3-bar-item w3-button active">Transaction History</a>
        </div>
    </nav>
    <!-- Main Content/Crud -->
    <div class="content-box">
        <div class="top">
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
                              FROM appointments 
                              WHERE DATE(date) = '$today'";

                $result_today = mysqli_query($con, $sql_today);

                // Check for SQL errors
                if (!$result_today) {
                    die("Query failed: " . mysqli_error($con));
                }

                $row_today = mysqli_fetch_assoc($result_today);
                $appointments_today = $row_today['total_appointments_today'];

                echo $appointments_today ? $appointments_today : 'No data available';
                ?>
            </div>
            <div class="round-box">
                <p>PENDING APPOINTMENTS:</p>
                <?php
                // Query to count pending appointments
                $sql_pending = "SELECT COUNT(*) as total_pending_appointments 
                                FROM appointments 
                                WHERE status = 'pending'";
                $result_pending = mysqli_query($con, $sql_pending);

                // Check for SQL errors
                if (!$result_pending) {
                    die("Query failed: " . mysqli_error($con));
                }

                $row_pending = mysqli_fetch_assoc($result_pending);
                $pending_appointments = $row_pending['total_pending_appointments'];

                echo $pending_appointments ? $pending_appointments : 'No data available';
                ?>
            </div>
            <div class="round-box">
                <p>APPOINTMENT FOR THE WEEK:</p>
                <?php
                // Get the start and end date of the current week
                $start_of_week = date('Y-m-d', strtotime('monday this week'));
                $end_of_week = date('Y-m-d', strtotime('sunday this week'));

                // Query to count appointments for the current week
                $sql_week = "SELECT COUNT(*) as total_appointments_week 
                             FROM appointments 
                             WHERE DATE(date) BETWEEN '$start_of_week' AND '$end_of_week'";
                $result_week = mysqli_query($con, $sql_week);

                // Check for SQL errors
                if (!$result_week) {
                    die("Query failed: " . mysqli_error($con));
                }

                $row_week = mysqli_fetch_assoc($result_week);
                $appointments_for_week = $row_week['total_appointments_week'];

                echo $appointments_for_week ? $appointments_for_week : 'No data available';
                ?>
            </div>
            <div class="round-box">
                <p>FINISHED APPOINTMENTS:</p>
                <?php
                // Query to count finished appointments
                $sql_finished = "SELECT COUNT(*) as total_finished_appointments FROM appointments WHERE status = 'finished'";
                $result_finished = mysqli_query($con, $sql_finished);

                // Check for SQL errors
                if (!$result_finished) {
                    die("Query failed: " . mysqli_error($con));
                }

                $row_finished = mysqli_fetch_assoc($result_finished);
                $finished_appointments = $row_finished['total_finished_appointments'];

                echo $finished_appointments ? $finished_appointments : 'No data available';
                ?>
            </div>
        </div>
        <h2>Transaction History</h2>

        <!-- Button to open the modal -->
        <button id="openModalBtn" class="add-transaction-btn">Add New Transaction</button>

        <!-- Modal for adding new transactions -->
        <div id="transactionModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Add New Transaction</h2>
                <form method="POST" action="">
                    <label>Name of Patient:</label>
                    <input type="text" name="patient_name" required><br>

                    <label>Contact No.:</label>
                    <input type="text" name="contact_no" required><br>

                    <label>Type of Service:</label>
                    <input type="text" name="type_of_service" required><br>

                    <label>Date:</label>
                    <input type="date" name="date_of_service" required><br>

                    <label>Bill:</label>
                    <input type="number" name="bill" step="0.01" required><br>

                    <label>Change:</label>
                    <input type="number" name="change_amount" step="0.01" required><br>

                    <label>Outstanding Balance:</label>
                    <input type="number" name="outstanding_balance" step="0.01" required><br>

                    <button type="submit">Add Transaction</button>
                </form>
            </div>
        </div>

        <!-- Transaction History Table -->
        <table>
            <tr>
                <th>Name of Patient</th>
                <th>Contact No.</th>
                <th>Type of Service</th>
                <th>Date</th>
                <th>Bill</th>
                <th>Change</th>
                <th>Outstanding Balance</th>
            </tr>
            <?php
            // Fetch and display transaction history
            $sql = "SELECT * FROM transaction_history";
            $result = mysqli_query($con, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['patient_name']}</td>
                        <td>{$row['contact_no']}</td>
                        <td>{$row['type_of_service']}</td>
                        <td>{$row['date_of_service']}</td>
                        <td>{$row['bill']}</td>
                        <td>{$row['change_amount']}</td>
                        <td>{$row['outstanding_balance']}</td>
                      </tr>";
            }
            ?>
        </table>
        <script>
            // Get modal element
            var modal = document.getElementById("transactionModal");
            var openModalBtn = document.getElementById("openModalBtn");
            var closeModalSpan = document.getElementsByClassName("close")[0];

            // Open modal when button is clicked
            openModalBtn.onclick = function () {
                modal.style.display = "block";
            }

            // Close modal when 'x' is clicked
            closeModalSpan.onclick = function () {
                modal.style.display = "none";
            }

            // Close modal when clicking outside of modal
            window.onclick = function (event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        </script>
    </div>

</body>

</html>