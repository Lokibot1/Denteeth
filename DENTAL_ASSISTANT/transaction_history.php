<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['3'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
include("../dbcon.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_SESSION['form_submitted'])) {
    // Prepare and bind
    $stmt = $con->prepare("INSERT INTO tbl_transaction_history (name, contact, service_type, date, time, bill, change_amount, outstanding_balance) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssddd", $patient_name, $contact_no, $service_type, $date, $time, $bill, $change_amount, $outstanding_balance);

    // Get and sanitize values from POST
    $name = trim($_POST['first_name'] . ' ' . $_POST['last_name']); // Combine first and last name
    $contact= trim($_POST['contact']);
    $service_type = trim($_POST['service_type']);
    $date = trim($_POST['date']);
    $time = trim($_POST['time']);
    $bill = (float)$_POST['bill'];
    $change_amount = (float)$_POST['change_amount'];
    $outstanding_balance = (float)$_POST['outstanding_balance'];

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
            <br><hr><br>
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
        <button id="openModalBtn" class="add-transaction-btn">Add New Transaction</button>

        <!-- Modal for adding new transactions -->
        <div id="transactionModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Add New Transaction</h2>
                <form method="POST" action="">
                    <label for="modal-first-name">First Name:</label>
                    <input type="text" name="first_name" id="modal-first-name" required>
                    <br>
                    <label for="modal-last-name">Last Name:</label>
                    <input type="text" name="last_name" id="modal-last-name" required>
                    <br>
                    <label for="modal-middle-name">Middle Name:</label>
                    <input type="text" name="middle_name" id="modal-middle-name" required>
                    <br>
                    <label for="modal-contact">Contact:</label>
                    <input type="text" name="contact" id="modal-contact" required>
                    <br>
                    <label>Type of Service:</label>
                    <input type="text" name="type_of_service" required><br>
                    <label for="modal-date">Date:</label>
                    <input type="date" name="date" id="modal-date" required>
                    <br>
                    <p>
                        <label for="modal-time">Time:</label>
                        <input type="time" name="time" id="modal-time" required>
                    </p>
                    <label for="modal-bill">Bill:</label>
                    <input type="number" step="0.01" name="bill" id="modal-bill" required>
                    <br>
                    <label for="modal-change-amount">Change Amount:</label>
                    <input type="number" step="0.01" name="change_amount" id="modal-change-amount" required>
                    <br>
                    <label for="modal-outstanding-balance">Outstanding Balance:</label>
                    <input type="number" step="0.01" name="outstanding_balance" id="modal-outstanding-balance" required>
                    <br>
                    <input type="submit" value="Add Transaction">
                </form>
            </div>
        </div>

        <table class="transaction-table">
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Contact </th>
                    <th>Type of Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Bill</th>
                    <th>Change Amount</th>
                    <th>Outstanding Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM tbl_transaction_history";
                $result = mysqli_query($con, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>{$row['name']}</td>
                                <td>{$row['contact']}</td>
                                <td>{$row['service_type']}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['time']}</td>
                                <td>{$row['bill']}</td>
                                <td>{$row['change_amount']}</td>
                                <td>{$row['outstanding_balance']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No transactions found</td></tr>";
                }

                // Close the database connection
                mysqli_close($con);
                ?>
            </tbody>
        </table>
    </div>

    <script>
        // JavaScript to open and close modal
        const modal = document.getElementById("transactionModal");
        const openModalBtn = document.getElementById("openModalBtn");

        openModalBtn.onclick = function () {
            modal.style.display = "block";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>
