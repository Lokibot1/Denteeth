<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['2'])) {
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
                                 SET contact='$contact', modified_date='$modified_date', modified_time='$modified_time',     service_type='$service_type' 
                                 WHERE id=$id";  // Assuming patient_id is used as foreign key in tbl_appointments

    // Execute both queries
    if (mysqli_query($con, $update_patient_query) && mysqli_query($con, $update_appointment_query)) {
        // Redirect to the same page after updating
        header("Location: appointments  .php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}

if (isset($_POST['submit'])) {
    // Get and sanitize the posted data
    $id = intval($_POST['id']); // Get the appointment ID
    $recommendation = mysqli_real_escape_string($con, $_POST['recommendation']);
    $price = floatval($_POST['price']); // Get the price

    // Fetch appointment details from the database
    $fetch_query = "SELECT * FROM tbl_appointments WHERE id = $id";
    $result = mysqli_query($con, $fetch_query);

    if ($result && mysqli_num_rows($result) > 0) {
        $appointment = mysqli_fetch_assoc($result);

        // Insert the appointment data into the tbl_archives table
        $archive_query = "INSERT INTO tbl_archives (name, contact, date, time, modified_date, modified_time, service_type, recommendation, price, status)
                          VALUES ('{$appointment['name']}', '{$appointment['contact']}', '{$appointment['date']}', '{$appointment['time']}', '{$appointment['modified_date']}', '{$appointment['modified_time']}', '{$appointment['service_type']}', '{$recommendation}', '{$price}', '1')";

        if (!mysqli_query($con, $archive_query)) {
            die("Error inserting into tbl_archives: " . mysqli_error($con));
        }

        // Update the appointment status to 'finished' in tbl_appointments
        $update_status_query = "UPDATE tbl_appointments SET status = '2' WHERE id = $id";
        if (!mysqli_query($con, $update_status_query)) {
            die("Error updating appointment status: " . mysqli_error($con));
        }

        // Handle additional services using the new connection
        if (isset($_POST['additional_services'])) {
            $additional_services_ids = array_map('intval', $_POST['additional_services']); // Sanitize input
            $values = [];

            // Prepare the values for the insert query
            foreach ($additional_services_ids as $service_id) {
                $values[] = "($service_id)"; // Prepare each value for insertion
            }

            // Create the insert query for additional services
            if (!empty($values)) {
                $insert_additional_service_query = "INSERT INTO tbl_additional_service_types (additional_service_type_1) VALUES " . implode(',', $values);

                if (!mysqli_query($additional_services_con, $insert_additional_service_query)) {
                    die("Error inserting additional services: " . mysqli_error($additional_services_con));
                }
            }
        }

        // Redirect back to appointments page
        header("Location: appointments.php");
        exit();
    } else {
        die("Appointment not found.");
    }
}

if (isset($_POST['decline'])) {
    $id = $_POST['id'];
    $deleteQuery = "UPDATE tbl_appointments SET status = '2' WHERE id = $id";
    mysqli_query($con, $deleteQuery);

    // Redirect to refresh the page and show updated records
    header("Location: appointments.php");
}

// SQL query to count total records
$countQuery = "SELECT COUNT(*) as total FROM tbl_appointments WHERE status = '1'";
$countResult = mysqli_query($con, $countQuery);
$totalCount = mysqli_fetch_assoc($countResult)['total'];

// SQL query with JOIN to fetch the limited number of records
$query = "SELECT a.*, 
            s.service_type AS service_name, 
            p.first_name, p.middle_name, p.last_name
          FROM tbl_appointments a
          JOIN tbl_service_type s ON a.service_type = s.id
          JOIN tbl_patient p ON a.id = p.id
          WHERE a.status = '3'
          LIMIT 15";  // Limit to 15 rows

$result = mysqli_query($con, $query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="doctor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Doctor Dashboard</title>
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
        </a>
    </nav>
    <div>
        <aside class="sidebar">
            <ul>
                <br>
                <a href="doctor_dashboard.php">
                    <h3>DOCTOR <br>DASHBOARD</h3>
                </a>
                <br>
                <br>
                <hr>
                <br>
                <li><a href="appointments.php">Approved Appointments</a></li>
                <li><a href="week.php">Appointment for the next week</a></li>
                <li><a href="services.php">Services</a></li>
            </ul>
        </aside>
    </div>
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

            <?php
            // Set the number of results per page
            $resultsPerPage = 7;

            // Get the current page number from query parameters, default to 1
            $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            // Calculate the starting row for the SQL query
            $startRow = ($currentPage - 1) * $resultsPerPage;

            // Get today's date
            $today = date('Y-m-d');

            // SQL query to count total records for Day
            $countQueryDay = "SELECT COUNT(*) as total FROM tbl_appointments WHERE (DATE(date) = CURDATE() OR DATE(modified_date) = CURDATE()) AND status = '3'";
            $countResultDay = mysqli_query($con, $countQueryDay);
            $totalCountDay = mysqli_fetch_assoc($countResultDay)['total'];
            $totalPagesDay = ceil($totalCountDay / $resultsPerPage); // Calculate total pages for Day
            
            // SQL query to count total records for Week
            $start_of_week = date('Y-m-d', strtotime('last Sunday')); // Get the start of the week
            $end_of_week = date('Y-m-d', strtotime('next Saturday')); // Get the end of the week
            
            $countQueryWeek = "SELECT COUNT(*) as total FROM tbl_appointments 
                    WHERE (DATE(date) BETWEEN '$start_of_week' AND '$end_of_week' 
                    OR DATE(modified_date) BETWEEN '$start_of_week' AND '$end_of_week') 
                    AND status = '3'";
            $countResultWeek = mysqli_query($con, $countQueryWeek);
            $totalCountWeek = mysqli_fetch_assoc($countResultWeek)['total'];
            $totalPagesWeek = ceil($totalCountWeek / $resultsPerPage); // Calculate total pages for Week
            
            // SQL query for Day with JOIN to fetch the limited number of records with OFFSET
            $queryDay = "SELECT a.*, 
                s.service_type AS service_name, 
                p.first_name, p.middle_name, p.last_name 
              FROM tbl_appointments a
              JOIN tbl_service_type s ON a.service_type = s.id
              JOIN tbl_patient p ON a.id = p.id
              WHERE (DATE(a.date) = '$today' OR DATE(a.modified_date) = '$today') AND a.status = '3'
              ORDER BY a.date DESC, a.time DESC, a.modified_date DESC, a.modified_time DESC
              LIMIT $resultsPerPage OFFSET $startRow";

            // SQL query for Week with JOIN to fetch the limited number of records with OFFSET
            $queryWeek = "SELECT a.*, 
                      s.service_type AS service_name, 
                      p.first_name, p.middle_name, p.last_name 
              FROM tbl_appointments a
              JOIN tbl_service_type s ON a.service_type = s.id
              JOIN tbl_patient p ON a.id = p.id  -- corrected join condition (assuming the link should be `a.patient_id = p.id`)
              WHERE ((WEEK(DATE(a.date), 1) = WEEK(CURDATE(), 1) AND DATE(a.date) != CURDATE()) 
              OR (WEEK(DATE(a.modified_date), 1) = WEEK(CURDATE(), 1) AND DATE(a.modified_date) != CURDATE())) 
              AND a.status = '3'
              ORDER BY a.date DESC, a.time DESC, a.modified_date DESC, a.modified_time DESC
              LIMIT $resultsPerPage OFFSET $startRow";

            $resultWeek = mysqli_query($con, $queryWeek);
            $resultDay = mysqli_query($con, $queryDay);
            ?>

            <!-- HTML Table and Tab structure -->

            <div class="pagination-container">
                <!-- Day Pagination -->
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?>" class="pagination-btn">
                        << /a>
                        <?php endif; ?>
                        <?php if ($currentPage < $totalPagesDay): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?>" class="pagination-btn">></a>
                        <?php endif; ?>
            </div>
            <br>
            <div class="tab">
                <button class="tablinks" onclick="openTab(event, 'Day')">Today</button>
                <button class="tablinks" onclick="openTab(event, 'Week')">This Week</button>
                <button class="tablinks" onclick="openTab(event, 'Week')">Next Week</button>
            </div>

            <!-- Tab content for Day -->
            <div id="Day" class="tabcontent" style="display: block;">
                <h3>Today</h3>
                <br>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Type Of Service</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($resultDay) > 0) {
                            while ($row = mysqli_fetch_assoc($resultDay)) {
                                // Use modified_date and modified_time if available, otherwise fallback to original date and time
                                $modified_date = !empty($row['modified_date']) ? $row['modified_date'] : $row['date'];
                                $modified_time = !empty($row['modified_time']) ? date("h:i A", strtotime($row['modified_time'])) : (!empty($row['time']) ? date("h:i A", strtotime($row['time'])) : 'N/A');

                                $dateToDisplay = !empty($row['date']) ? $row['date'] : 'N/A';
                                $timeToDisplay = !empty($row['time']) ? date("h:i A", strtotime($row['time'])) : 'N/A';

                                echo "<tr>
            <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
            <td>{$row['contact']}</td>
            <td>{$modified_date}</td>
            <td>{$modified_time}</td>
            <td>{$row['service_name']}</td>
            <td>
                <button type='button' onclick='openModal({$row['id']}, \"{$row['first_name']}\", \"{$row['middle_name']}\", \"{$row['last_name']}\", \"{$row['contact']}\", \"{$dateToDisplay}\", \"{$timeToDisplay}\", \"{$row['service_name']}\")' 
                style='background-color:#083690; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>Update</button>
                <form method='POST' action='' style='display:inline;'>
                    <input type='hidden' name='id' value='{$row['id']}'>
                    <input type='submit' name='decline' value='Decline' onclick=\"return confirm('Are you sure you want to remove this record?');\" 
                    style='background-color: rgb(196, 0, 0); color:white; border:none;  padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
                </form>";

                                if ($row['status'] != 'finished') {
                                    echo "<form method='POST' action='' style='display:inline;'>
                <input type='hidden' name='id' value='{$row['id']}'>
                <input type='submit' name='finish' value='Finish' 
                style='background-color:green; color:white; border:none;  padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
            </form>";
                                }

                                echo "</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Tab content for Week -->
            <div id="Week" class="tabcontent" style="display: none;">
                <h3>This Week</h3>
                <br>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Type Of Service</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($resultWeek) > 0) {
                            while ($row = mysqli_fetch_assoc($resultWeek    )) {
                                // Use modified_date and modified_time if available, otherwise fallback to original date and time
                                $modified_date = !empty($row['modified_date']) ? $row['modified_date'] : $row['date'];
                                $modified_time = !empty($row['modified_time']) ? date("h:i A", strtotime($row['modified_time'])) : (!empty($row['time']) ? date("h:i A", strtotime($row['time'])) : 'N/A');

                                $dateToDisplay = !empty($row['date']) ? $row['date'] : 'N/A';
                                $timeToDisplay = !empty($row['time']) ? date("h:i A", strtotime($row['time'])) : 'N/A';

                                echo "<tr>
            <td>{$row['last_name']}, {$row['first_name']} {$row['middle_name']}</td>
            <td>{$row['contact']}</td>
            <td>{$modified_date}</td>
            <td>{$modified_time}</td>
            <td>{$row['service_name']}</td>
            <td>
                <button type='button' onclick='openModal({$row['id']}, \"{$row['first_name']}\", \"{$row['middle_name']}\", \"{$row['last_name']}\", \"{$row['contact']}\", \"{$dateToDisplay}\", \"{$timeToDisplay}\", \"{$row['service_name']}\")' 
                style='background-color:#083690; color:white; border:none; padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>Update</button>
                <form method='POST' action='' style='display:inline;'>
                    <input type='hidden' name='id' value='{$row['id']}'>
                    <input type='submit' name='decline' value='Decline' onclick=\"return confirm('Are you sure you want to remove this record?');\" 
                    style='background-color: rgb(196, 0, 0); color:white; border:none;  padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
                </form>";

                                if ($row['status'] != 'finished') {
                                    echo "<form method='POST' action='' style='display:inline;'>
                <input type='hidden' name='id' value='{$row['id']}'>
                <input type='submit' name='finish' value='Finish' 
                style='background-color:green; color:white; border:none;  padding:7px 9px; border-radius:10px; margin:11px 3px; cursor:pointer;'>
            </form>";
                                }

                                echo "</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal Structure -->
            <div id="finishModal" class="modal" style="display: none;">
                <div class="modal-content"
                    style="width: 500px; margin: auto; background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                    <span class="close" style="float: right; cursor: pointer; " onclick="closeModal()">&times;</span>
                    <h3 style="text-align: center;">Service Completion</h3>
                    <hr>
                    <!-- Display Selected Information -->
                    <div id="modalDetails">
                        <p><strong>Name:</strong> <span id="modalName"></span></p>
                        <p><strong>Contact Number:</strong> <span id="modalContact"></span></p>
                        <p><strong>Date & Time:</strong> <span id="modalDateTime"></span></p>
                        <p><strong>Current Service:</strong> <span id="modalService"></span></p>
                    </div>
                    <hr>
                    <button id="addServiceButton" onclick="addServiceDropdown()">Add More Services</button>
                    <div id="servicesContainer"></div>
                    <form id="newServiceForm" method="POST" action="">
                        <input type="hidden" name="id" value="">
                        <label for="recommendation">Recommendation:</label>
                        <textarea id="recommendation" name="recommendation"
                            placeholder="Enter your recommendation here..."></textarea>
                        <div id="totalPriceContainer">
                            <p><strong>Total Price: ₱</strong><span id="totalPrice">0</span></p>
                        </div>
                        <input type="number" id="price" name="price" style="display: none;" readonly>
                        <button type="submit" name="submit">Submit</button>
                    </form>
                </div>
            </div>

            <!-- JavaScript for Modal and Dropdown -->
            <script>
                let totalPrice = 0; // Track the total price of selected services
                const servicePrices = {
                    1: 30000, 2: 30000, 3: 2000, 4: 100000, 5: 20000,
                    6: 30000, 7: 1500, 8: 2000, 9: 280000, 10: 40000, 11: 40000
                };

                function openFinishModal(id, firstName, middleName, lastName, contact, date, time, service) {
                    // Populate modal details
                    document.getElementById('modalName').innerText = `${lastName}, ${firstName} ${middleName}`;
                    document.getElementById('modalContact').innerText = contact;
                    document.getElementById('modalDateTime').innerText = `${date} at ${time}`;
                    document.getElementById('modalService').innerText = service;

                    const serviceId = getServiceIdFromName(service);
                    const servicePrice = servicePrices[serviceId] || 0;
                    document.getElementById('price').value = servicePrice;
                    totalPrice += servicePrice;
                    document.getElementById('totalPrice').innerText = totalPrice;

                    document.getElementById('finishModal').style.display = 'block';
                }

                function closeModal() {
                    document.getElementById('finishModal').style.display = 'none';
                }

                function getServiceIdFromName(serviceName) {
                    const services = {
                        "All Porcelain Veneers & Zirconia": 1,
                        "Crown & Bridge": 2,
                        "Dental Cleaning": 3,
                        "Dental Implants": 4,
                        "Dental Whitening": 5,
                        "Dentures": 6,
                        "Extraction": 7,
                        "Full Exam & X-Ray": 8,
                        "Orthodontic Braces": 9,
                        "Restoration": 10,
                        "Root Canal Treatment": 11
                    };
                    return services[serviceName] || null;
                }

                function addServiceDropdown() {
                    const servicesContainer = document.getElementById('servicesContainer');
                    const newServiceDiv = document.createElement('div');
                    const serviceSelect = document.createElement('select');
                    const services = [
                        { value: 1, label: "All Porcelain Veneers & Zirconia" },
                        { value: 2, label: "Crown & Bridge" },
                        { value: 3, label: "Dental Cleaning" },
                        { value: 4, label: "Dental Implants" },
                        { value: 5, label: "Dental Whitening" },
                        { value: 6, label: "Dentures" },
                        { value: 7, label: "Extraction" },
                        { value: 8, label: "Full Exam & X-Ray" },
                        { value: 9, label: "Orthodontic Braces" },
                        { value: 10, label: "Restoration" },
                        { value: 11, label: "Root Canal Treatment" }
                    ];

                    services.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.value;
                        option.innerText = service.label;
                        serviceSelect.appendChild(option);
                    });

                    serviceSelect.addEventListener('change', function () {
                        const serviceId = parseInt(this.value);
                        const servicePrice = servicePrices[serviceId];
                        totalPrice += servicePrice;
                        document.getElementById('totalPrice').innerText = totalPrice;
                    });

                    newServiceDiv.appendChild(serviceSelect);
                    servicesContainer.appendChild(newServiceDiv);
                }
            </script>


            <!-- Edit Modal -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <form method="POST" action="">
                        <h1>EDIT DETAILS</h1><br>
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
                        <input type="text" name="contact" id="modal-contact" placeholder="Enter your contact number"
                            maxlength="11" required pattern="\d{11}" title="Please enter exactly 11 digits"><br>
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
                function openUpdateModal(id, first_name, middle_name, last_name, contact, modified_date, modified_time, service_type) {
                    document.getElementById('modal-id').value = id;
                    document.getElementById('modal-first-name').value = first_name;
                    document.getElementById('modal-middle-name').value = middle_name;
                    document.getElementById('modal-last-name').value = last_name;
                    document.getElementById('modal-contact').value = contact;
                    document.getElementById('modal-modified_date').value = modified_date;
                    document.getElementById('modal-modified_time').value = modified_time;
                    document.getElementById('modal-service_type').value = service_type;
                    document.getElementById('editModal').style.display = 'block';
                }

                // Close the modal
                function closeModal() {
                    document.getElementById('editModal').style.display = 'none';
                }

                // Switch between tabs
                function openTab(evt, tabName) {
                    var i, tabcontent, tablinks;
                    tabcontent = document.getElementsByClassName("tabcontent");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }
                    tablinks = document.getElementsByClassName("tablinks");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].classList.remove("active");
                    }
                    document.getElementById(tabName).style.display = "block";
                    evt.currentTarget.classList.add("active");
                }
            </script>

        </div>
    </div>
</body>

</html>