<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'doctor'])) {
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
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $date = mysqli_real_escape_string($con, $_POST['date']);
    $time = mysqli_real_escape_string($con, $_POST['time']);
    $service_type = mysqli_real_escape_string($con, $_POST['service_type']);

    // Prepare the update query
    $update_query = "UPDATE appointments SET fname='$fname', contact='$contact', date='$date', time='$time', service_type='$service_type' WHERE id=$id";

    // Execute the query
    if (mysqli_query($con, $update_query)) {
        // Redirect to the same page after updating
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

if (isset($_POST['finish'])) {
    // Get the appointment ID from the form
    $id = $_POST['id'];

    // Prepare the query to update the status to 'finished'
    $update_query = "UPDATE appointments SET status='finished' WHERE id=$id";

    // Execute the query
    if (mysqli_query($con, $update_query)) {
        // Redirect back to the dashboard or the page you're currently on
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo "Error updating status: " . mysqli_error($con);
    }
}

if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    // Fetch the appointment data to insert into appointments_bin
    $fetch_query = "SELECT * FROM appointments WHERE id=$id";
    $result = mysqli_query($con, $fetch_query);
    if ($result && mysqli_num_rows($result) > 0) {
        $appointment = mysqli_fetch_assoc($result);

        // Insert into appointments_bin
        $insert_query = "INSERT INTO appointments_bin (fname, contact, date, time, service_type, status)
                         VALUES ('{$appointment['fname']}', '{$appointment['contact']}', '{$appointment['date']}', '{$appointment['time']}', '{$appointment['service_type']}', 'deleted')";
        mysqli_query($con, $insert_query);
    }

    // Delete the appointment from appointments table
    $delete_query = "DELETE FROM appointments WHERE id=$id";

    // Execute the delete query
    if (mysqli_query($con, $delete_query)) {
        // Redirect back to the dashboard or the page you're currently on
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($con);
    }
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
            <a href="week.php" class="w3-bar-item w3-button">Appointment for the week</a>
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
                              FROM appointments 
                              WHERE status = 'accepted' AND DATE(date) = '$today'";

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
                             FROM appointments 
                             WHERE status = 'accepted' AND DATE(date) BETWEEN '$start_of_week' AND '$end_of_week'";
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
                    $result = mysqli_query($con, "SELECT * FROM appointments WHERE status = 'accepted'");

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                    <td>{$row['fname']}</td>
                    <td>{$row['contact']}</td>
                    <td>{$row['date']}</td>
                    <td>{$row['time']}</td>
                    <td>{$row['service_type']}</td>
                    <td>
                        <button type='button' onclick='openModal({$row['id']}, \"{$row['fname']}\", \"{$row['contact']}\", \"{$row['date']}\", \"{$row['time']}\", \"{$row['service_type']}\")'>Edit</button>
                        <form method='POST' action='' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <input type='submit' name='delete' value='Delete' onclick=\"return confirm('Are you sure you want to delete this record?');\">
                        </form>";

                            // Only display the 'Finish' button if the status is not 'finished'
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
                        <label for="fname">Name:</label>
                        <input type="text" name="fname" id="modal-fname" required><br>
                        <label for="contact">Contact:</label>
                        <input type="text" name="contact" id="modal-contact" required><br>
                        <label for="date">Date:</label>
                        <input type="date" name="date" id="modal-date" required><br>
                        <label for="time">Time:</label>
                        <input type="time" name="time" id="modal-time" required><br>
                        <label for="service_type">Type Of Service:</label>
                        <select name="service_type" id="modal-service_type" required>
                            <option value="">--Select Service Type--</option>
                            <option value="All Porcelain Veneers & Zirconia">All Porcelain Veneers & Zirconia</option>
                            <option value="Crown & Bridge">Crown & Bridge</option>
                            <option value="Dental Cleaning">Dental Cleaning</option>
                            <option value="Dental Implants">Dental Implants</option>
                            <option value="Dental Whitening">Dental Whitening</option>
                            <option value="Dentures">Dentures</option>
                            <option value="Extraction">Extraction</option>
                            <option value="Full Exam & X-Ray">Full Exam & X-Ray</option>
                            <option value="Orthodontic Braces">Orthodontic Braces</option>
                            <option value="Restoration">Restoration</option>
                            <option value="Root Canal Treatment">Root Canal Treatment</option>
                        </select><br>
                        <input type="submit" name="update" value="Save">
                    </form>
                </div>
            </div>

            <script>
                // Open the modal and populate it with data
                function openModal(id, fname, contact, date, time, service_type) {
                    document.getElementById('modal-id').value = id;
                    document.getElementById('modal-fname').value = fname;
                    document.getElementById('modal-contact').value = contact;
                    document.getElementById('modal-date').value = date;
                    document.getElementById('modal-time').value = time;
                    document.getElementById('modal-service_type').value = service_type;

                    // Restrict date to current week, starting from Monday
                    const today = new Date();
                    const dayOfWeek = today.getDay();
                    const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // Adjust if today is Sunday (day 0)
                    const firstDay = new Date(today.setDate(today.getDate() + mondayOffset)); // Start of the week (Monday)
                    const lastDay = new Date(firstDay);
                    lastDay.setDate(firstDay.getDate() + 6); // End of the week (Sunday)

                    const currentDate = new Date(); // Current date for comparison

                    // Disable past dates within the current week
                    document.getElementById('modal-date').setAttribute('min', formatDate(firstDay));
                    document.getElementById('modal-date').setAttribute('max', formatDate(lastDay));

                    // If the date has already passed, disable it
                    if (new Date(date) < currentDate) {
                        document.getElementById('modal-date').classList.add('disabled-date');
                        document.getElementById('modal-date').setAttribute('disabled', true); // Make unselectable
                    } else {
                        document.getElementById('modal-date').classList.remove('disabled-date');
                        document.getElementById('modal-date').removeAttribute('disabled'); // Make selectable
                    }

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