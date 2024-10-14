<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['1', '2'])) {
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
    $update_appointment_query = "UPDATE tbl_appointments 
                                 SET contact='$contact', modified_date='$modified_date', modified_time='$modified_time', service_type='$service_type' 
                                 WHERE id=$id";  // Assuming patient_id is used as foreign key in tbl_appointments

    // Execute both queries
    if (mysqli_query($con, $update_patient_query) && mysqli_query($con, $update_appointment_query)) {
        // Redirect to the same page after updating
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}


if (isset($_POST['finish'])) {
    // Check if the connection exists
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get the appointment ID from the form
    $id = $_POST['id'];

    // Prepare the query to update the status to 'finished' using a prepared statement
    $stmt = $con->prepare("UPDATE tbl_appointments SET status=? WHERE id=?");
    $status = 4; // Assuming '4' represents finished
    $stmt->bind_param("ii", $status, $id);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect back to the dashboard
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    $stmt->close();
}

if (isset($_POST['declined'])) {
    // Check if the connection exists
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $id = $_POST['id'];

    // Fetch the appointment data to insert into appointments_bin
    $stmt = $con->prepare("SELECT * FROM tbl_appointments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $appointment = $result->fetch_assoc();

        // Insert into appointments_bin
        $insert_stmt = $con->prepare("INSERT INTO tbl_appointments_bin (name, contact, date, time, modified_date, modified_time, service_type, status)
                                      VALUES (?, ?, ?, ?, ?, ?)");
        $status = '2';
        $insert_stmt->bind_param("ssssss", $appointment['name'], $appointment['contact'], $appointment['date'], $appointment['time'], $appointment['modified_date'], $appointment['modified_time'], $appointment['service_type'], $status);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    // Delete the appointment from the appointments table
    $delete_stmt = $con->prepare("DELETE FROM tbl_appointments WHERE id=?");
    $delete_stmt->bind_param("i", $id);

    // Execute the delete query
    if ($delete_stmt->execute()) {
        // Redirect back to the dashboard
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo "Error deleting record: " . $delete_stmt->error;
    }

    $stmt->close();
    $delete_stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="doctor_dashboard.css">
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
            <a href="week.php" class="w3-bar-item w3-button active">Appointment for the week</a>
            <a href="finished.php" class="w3-bar-item w3-button">Finished Appointments</a>
            <a href="services.php" class="w3-bar-item w3-button">Services</a>
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
                              FROM tbl_appointments 
                              WHERE (DATE(date) = '$today' OR DATE(modified_date) = '$today') AND status = '3'";



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
                <p>APPOINTMENT FOR THE WEEK:</p>
                <?php
                // Get the start and end date of the current week
                $start_of_week = date('Y-m-d', strtotime('monday this week'));
                $end_of_week = date('Y-m-d', strtotime('sunday this week'));

                // Query to count appointments for the current week
                $sql_week = "SELECT COUNT(*) as total_appointments_week 
                 FROM tbl_appointments 
                 WHERE (DATE(date) BETWEEN '$start_of_week' AND '$end_of_week' 
                 OR DATE(modified_date) BETWEEN '$start_of_week' AND '$end_of_week') 
                 AND status = '3'";

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
                $sql_finished = "SELECT COUNT(*) as total_finished_appointments FROM tbl_appointments WHERE status = '4'";
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
            <!-- HTML Table -->
            <div>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Type Of Service</th>
                        <th>Actions</th>
                    </tr>
                    <?php
                    // SQL query with JOIN to fetch the service type and full name from tbl_patient
                    $query = "SELECT a.*, 
                     s.service_type AS service_name, 
                     p.first_name, p.middle_name, p.last_name
                  FROM tbl_appointments a
                  JOIN tbl_service_type s ON a.service_type = s.id
                  JOIN tbl_patient p ON a.id = p.id  -- Ensure you're joining using patient_id
                  WHERE (DATE(a.date) BETWEEN '$start_of_week' AND '$end_of_week'  OR DATE(a.modified_date) BETWEEN '$start_of_week' AND '$end_of_week' ) AND a.status = '3'"; // Filter by today's date and accepted status
                    
                    $result = mysqli_query($con, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Check if modified_date and modified_time are empty
                            $dateToDisplay = !empty($row['modified_date']) ? $row['modified_date'] : $row['date'];
                            $timeToDisplay = !empty($row['modified_time']) ? $row['modified_time'] : $row['time'];

                            // Format time to HH:MM AM/PM
                            $timeToDisplayFormatted = date("h:i A", strtotime($timeToDisplay));

                            echo "<tr>
                    <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>  <!-- Display full name -->
                    <td>{$row['contact']}</td>
                    <td>{$dateToDisplay}</td>
                    <td>{$timeToDisplayFormatted}</td>
                    <td>{$row['service_name']}</td>
                    <td>
                        <button type='button' onclick='openModal({$row['id']}, \"{$row['first_name']}\", \"{$row['middle_name']}\", \"{$row['last_name']}\", \"{$row['contact']}\", \"{$dateToDisplay}\", \"{$timeToDisplayFormatted}\", \"{$row['service_name']}\")'>Edit</button>
                        <form method='POST' action='' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <input type='submit' name='declined' value='Declined' onclick=\"return confirm('Are you sure you want to delete this record?');\">
                        </form>";

                            if ($row['status'] != 'finished') {
                                echo "<form method='POST' action='' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <input type='submit' name='finish' value='Finish'>
                          </form>";
                            }

                            echo "</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No records found</td></tr>";
                    }
                    ?>
                </table>
            </div>


            <!-- Edit Modal -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="modal-id">
                        <br>
                        <label for="modal-first-name">First Name:</label>
                        <input type="text" name="first_name" id="modal-first-name" required>
                        <br>
                        <label for="modal-last-name">Last Name:</label>
                        <input type="text" name="last_name" id="modal-last-name" required>
                        <br>
                        <label for="modal-middle-name">Middle Name:</label>
                        <input type="text" name="middle_name" id="modal-middle-name" required>
                        <br>
                        <label for="contact">Contact:</label>
                        <input type="text" name="contact" id="modal-contact" required>
                        <br>
                        <label for="date">Date:</label>
                        <input type="date" name="modified_date" id="modal-modified_date" required>
                        <br>
                        <p>
                            <label for="time">Time:</label>
                            <input type="time" name="modified_time" id="modal-modified_time" min="09:00" max="18:00"
                                required>
                            CLINIC HOURS 9:00 AM TO 6:00 PM
                        </p>
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
                        <br>
                        <input type="submit" name="update" value="Save">
                    </form>
                </div>
            </div>

            <script>
                // Open the modal and populate it with data
                function openModal(id, first_name, middle_name, last_name, contact, modified_date, modified_time, service_type) {
                    // Populate modal fields with the received values
                    document.getElementById('modal-id').value = id;
                    document.getElementById('modal-first-name').value = first_name;
                    document.getElementById('modal-middle-name').value = middle_name;
                    document.getElementById('modal-last-name').value = last_name;
                    document.getElementById('modal-contact').value = contact;
                    document.getElementById('modal-modified_date').value = modified_date;
                    document.getElementById('modal-modified_time').value = modified_time;
                    document.getElementById('modal-service_type').value = service_type;

                    // Restrict date to the current week, starting from Monday
                    const today = new Date();
                    const dayOfWeek = today.getDay();
                    const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // Adjust if today is Sunday (day 0)
                    const firstDay = new Date(today.setDate(today.getDate() + mondayOffset)); // Start of the week (Monday)
                    const lastDay = new Date(firstDay);
                    lastDay.setDate(firstDay.getDate() + 6); // End of the week (Sunday)

                    // Disable past dates within the current week
                    document.getElementById('modal-modified_date').setAttribute('min', formatDate(firstDay));
                    document.getElementById('modal-modified_date').setAttribute('max', formatDate(lastDay));

                    // Set time input limits
                    document.getElementById('modal-modified_time').setAttribute('min', '09:00');
                    document.getElementById('modal-modified_time').setAttribute('max', '18:00');

                    // Show the modal
                    document.getElementById('editModal').style.display = 'block';
                }

                // Close the modal
                function closeModal() {
                    document.getElementById('editModal').style.display = 'none';
                }

                // Format date as YYYY-MM-DD
                function formatDate(date) {
                    const year = date.getFullYear();
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const day = date.getDate().toString().padStart(2, '0');
                    return `${year}-${month}-${day}`;
                }

                // Close modal when clicking outside of it
                window.onclick = function (event) {
                    if (event.target == document.getElementById('editModal')) {
                        closeModal();
                    }
                }
            </script>
        </div>
    </div>
</body>

</html>