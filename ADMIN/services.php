<?php
session_start();


// Check if the user is logged in and has the required role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['1'])) {
    header("Location: ../login.php");
    exit();
}

include("../dbcon.php"); // Your database connection

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

function fetchService($con, $service_name)
{
    $sql = "SELECT * FROM tbl_services WHERE service_name = ?";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        echo "Error preparing statement: " . $con->error;
        return null; // Return null on failure
    }

    // Bind parameter
    $stmt->bind_param("s", $service_name);

    // Execute the statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        // Check if there are results
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Return the fetched data
        } else {
            return null; // No results found
        }
    } else {
        echo "Error executing statement: " . $stmt->error;
        return null; // Return null on execution failure
    }
}

$veneersData = fetchService($con, 'All Porcelain Veneers & Zirconia');
$crownBridgeData = fetchService($con, 'Crown & Bridge');
$cleaningData = fetchService($con, 'Dental Cleaning');
$implantsData = fetchService($con, 'Dental Implants');
$whiteningData = fetchService($con, 'Dental Whitening');
$dentureData = fetchService($con, 'Dentures');
$extractionData = fetchService($con, 'Extraction');
$examData = fetchService($con, 'Full Exam & X-Ray');
$bracesData = fetchService($con, 'Orthodontic Braces');
$restorationData = fetchService($con, 'Restoration');
$rootData = fetchService($con, 'Root Canal Treatment');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = $_POST['service_description'] ?? null;
    $partial_price_min = $_POST['partial_price_min'] ?? null;
    $partial_price_max = $_POST['partial_price_max'] ?? null;
    $complete_price_min = $_POST['complete_price_min'] ?? null;
    $complete_price_max = $_POST['complete_price_max'] ?? null;
    $service_name = $_POST['service_name'] ?? null;

    if (empty($service_name)) {
        echo "Service name is required.";
        exit();
    }

    // Handle image upload
    $target_file = null; // Initialize target_file as null
    if (!empty($_FILES["service_image"]["tmp_name"])) {
        $target_dir = "C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/";
        $target_file = $target_dir . basename($_FILES["service_image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an actual image
        $check = getimagesize($_FILES["service_image"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size (limit 500KB)
        if ($_FILES["service_image"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow only certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
            $target_file = null;
        } else {
            // Create the target directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Move the uploaded file
            if (!move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) {
                echo "Sorry, there was an error uploading your file.";
                $target_file = null;
            }
        }
    }

    // Build dynamic SQL query based on non-empty inputs
    $updates = [];
    $params = [];
    $types = "";

    if ($target_file) {
        $updates[] = "service_image = ?";
        $params[] = $target_file;
        $types .= "s";
    }
    if (!empty($description)) {
        $updates[] = "service_description = ?";
        $params[] = $description;
        $types .= "s";
    }
    if (!empty($partial_price_min)) {
        $updates[] = "partial_price_min = ?";
        $params[] = $partial_price_min;
        $types .= "d";
    }
    if (!empty($partial_price_max)) {
        $updates[] = "partial_price_max = ?";
        $params[] = $partial_price_max;
        $types .= "d";
    }
    if (!empty($complete_price_min)) {
        $updates[] = "complete_price_min = ?";
        $params[] = $complete_price_min;
        $types .= "d";
    }
    if (!empty($complete_price_max)) {
        $updates[] = "complete_price_max = ?";
        $params[] = $complete_price_max;
        $types .= "d";
    }

    // Only update if there are fields to update
    if (!empty($updates)) {
        $params[] = $service_name;
        $types .= "s";

        $sql = "UPDATE tbl_services SET " . implode(", ", $updates) . " WHERE service_name = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param($types, ...$params);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to prevent form resubmission
            header("Location: services.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "No fields to update.";
    }
}

// Close the connection
$con->close();
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
        <form method="POST" action="../logout.php">
            <button type="submit" class="logout-button">Logout</button>
        </form>
        <a href="admin_dashboard_bin.php"><i class="fas fa-trash trash"></i></a>
    </nav>
    <div>
        <aside class="sidebar">
            <ul>
                <br>
                <a class="active" href="admin_dashboard.php">
                    <h3>ADMIN<br>DASHBOARD</h3>
                </a>
                <br>
                <br>
                <hr>
                <br>
                <li><a href="pending.php">Pending Appointments</a></a></li>
                <li><a href="appointments.php">Approved Appointments</a></li>
                <li><a href="declined.php">Decline Appointments</a></a></li>
                <li><a href="billing.php">Billing Approval</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="manage_user.php">Manage Users</a></li>
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

                if ($finished_appointments) {
                    echo "<span style='color: #FF9F00; font-weight: bold; font-size: 25px;'>$finished_appointments</span>";
                } else {
                    echo "<span style='color: red;'>No data available</span>";
                }
                ?>
            </div>

            <h1>Services</h1>
            <div id="crvs-container">
                <!-- Img-box and Modal for All Services -->
                <div class="img-box" id="openModalBtnOrthodonticBraces">
                    <div class="img-wrapper">
                        <p>
                            <?php echo htmlspecialchars($bracesData ? $bracesData['service_name'] : 'Orthodontic Braces'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($bracesData ? basename($bracesData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($bracesData ? $bracesData['service_name'] : 'Orthodontic Braces'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>

                <!-- Modal Template -->
                <div id="serviceModalTemplate" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Service</h2>
                        <form id="serviceFormTemplate" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="serviceNameTemplate">
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputTemplate" accept="image/*"
                                onchange="previewImage(event, 'imagePreviewTemplate')"><br>
                            <img id="imagePreviewTemplate" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" id="serviceDescriptionTemplate"
                                required></textarea><br>
                            <label>Estimated Price:</label>
                            <input type="number" name="partial_price_min" id="partialPriceMinTemplate"
                                placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" id="partialPriceMaxTemplate"
                                placeholder="Max Price" required><br>
                            <label>Total Amount:</label>
                            <input type="number" name="complete_price_min" id="completePriceMinTemplate"
                                placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" id="completePriceMaxTemplate"
                                placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            // General Modal Elements and Functions for All Services
                            var modalTemplate = document.getElementById("serviceModalTemplate");
                            var spanTemplate = document.getElementsByClassName("close")[0];

                            // Function to handle modal population and open
                            function openServiceModal(serviceData) {
                                document.getElementById("serviceNameTemplate").value = serviceData.service_name || 'Service Name';
                                document.getElementById("serviceDescriptionTemplate").value = serviceData.service_description || '';
                                document.getElementById("partialPriceMinTemplate").value = serviceData.partial_price_min || '';
                                document.getElementById("partialPriceMaxTemplate").value = serviceData.partial_price_max || '';
                                document.getElementById("completePriceMinTemplate").value = serviceData.complete_price_min || '';
                                document.getElementById("completePriceMaxTemplate").value = serviceData.complete_price_max || '';
                                modalTemplate.style.display = "block";
                            }

                            // Function to reset modal
                            function resetModal(formId, previewId) {
                                document.getElementById(formId).reset();
                                document.getElementById(previewId).style.display = 'none';
                            }

                            // Close Modal
                            spanTemplate.onclick = function () {
                                resetModal('serviceFormTemplate', 'imagePreviewTemplate');
                                modalTemplate.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalTemplate) {
                                    resetModal('serviceFormTemplate', 'imagePreviewTemplate');
                                    modalTemplate.style.display = "none";
                                }
                            }

                            // Example: Open Modals for Each Service
                            // This is just an example for the Orthodontic Braces, you can use similar for others
                            document.getElementById("openModalBtnOrthodonticBraces").onclick = function () {
                                var bracesData = <?php echo json_encode($bracesData); ?>;
                                openServiceModal(bracesData);
                            };
                        </script>
                    </div>
                </div>

                <!-- Img-box and Modal for Dental Cleaning -->
                <div class="img-box" id="openModalBtnCleaning">
                    <div class="img-wrapper">
                        <p>
                            <?php echo htmlspecialchars($cleaningData ? $cleaningData['service_name'] : 'Dental Cleaning'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($cleaningData ? basename($cleaningData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($cleaningData ? $cleaningData['service_name'] : 'Dental Cleaning'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>

                <div id="serviceModalCleaning" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Dental Cleaning</h2>
                        <form id="serviceFormCleaning" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="serviceNameCleaning" value="Dental Cleaning">

                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputCleaning" accept="image/*"
                                onchange="previewImage(event, 'imagePreviewCleaning')"><br>
                            <img id="imagePreviewCleaning" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />

                            <label>Description:</label>
                            <textarea name="service_description" id="serviceDescriptionCleaning"
                                required></textarea><br>

                            <label>Estimated Price:</label>
                            <input type="number" name="partial_price_min" id="partialPriceMinCleaning"
                                placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" id="partialPriceMaxCleaning"
                                placeholder="Max Price" required><br>

                            <label>Total Amount:</label>
                            <input type="number" name="complete_price_min" id="completePriceMinCleaning"
                                placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" id="completePriceMaxCleaning"
                                placeholder="Max Price" required><br>

                            <button type="submit">Save Changes</button>
                        </form>

                        <script>
                            // Modal Elements
                            var modalCleaning = document.getElementById("serviceModalCleaning");
                            var btnCleaning = document.getElementById("openModalBtnCleaning");
                            var spanCleaning = document.getElementsByClassName("close")[1];

                            // Sample data object from PHP
                            var cleaningData = <?php echo json_encode($cleaningData); ?>;

                            // Function to populate modal with data
                            function populateModal(data) {
                                document.getElementById("serviceNameCleaning").value = data.service_name || 'Dental Cleaning';
                                document.getElementById("serviceDescriptionCleaning").value = data.service_description || '';
                                document.getElementById("partialPriceMinCleaning").value = data.partial_price_min || '';
                                document.getElementById("partialPriceMaxCleaning").value = data.partial_price_max || '';
                                document.getElementById("completePriceMinCleaning").value = data.complete_price_min || '';
                                document.getElementById("completePriceMaxCleaning").value = data.complete_price_max || '';
                            }

                            // Open Modal
                            btnCleaning.onclick = function () {
                                if (cleaningData) {
                                    populateModal(cleaningData);
                                }
                                modalCleaning.style.display = "block";
                            }

                            // Close Modal
                            spanCleaning.onclick = function () {
                                resetModal('serviceFormCleaning', 'imagePreviewCleaning');
                                modalCleaning.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalCleaning) {
                                    resetModal('serviceFormCleaning', 'imagePreviewCleaning');
                                    modalCleaning.style.display = "none";
                                }
                            }
                        </script>
                    </div>
                </div>

                <!-- Img-box and Modal for Dental Whitening -->
                <div class="img-box" id="openModalBtnWhitening">
                    <div class="img-wrapper">
                        <p>
                            <?php echo htmlspecialchars($whiteningData ? $whiteningData['service_name'] : 'Dental Whitening'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($whiteningData ? basename($whiteningData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($whiteningData ? $whiteningData['service_name'] : 'Dental Whitening'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>

                <!-- Modal Template for Dental Whitening -->
                <div id="serviceModalWhitening" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Dental Whitening</h2>
                        <form id="serviceFormWhitening" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="serviceNameWhitening">

                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputWhitening" accept="image/*"
                                onchange="previewImage(event, 'imagePreviewWhitening')"><br>
                            <img id="imagePreviewWhitening" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />

                            <label>Description:</label>
                            <textarea name="service_description" id="serviceDescriptionWhitening"
                                required></textarea><br>

                            <label>Estimated Price:</label>
                            <input type="number" name="partial_price_min" id="partialPriceMinWhitening"
                                placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" id="partialPriceMaxWhitening"
                                placeholder="Max Price" required><br>

                            <label>Total Amount:</label>
                            <input type="number" name="complete_price_min" id="completePriceMinWhitening"
                                placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" id="completePriceMaxWhitening"
                                placeholder="Max Price" required><br>

                            <button type="submit">Save Changes</button>
                        </form>

                        <script>
                            // General Modal Elements and Functions for All Services
                            var modalWhitening = document.getElementById("serviceModalWhitening");
                            var spanWhitening = document.getElementsByClassName("close")[2];

                            // Function to handle modal population and open
                            function openServiceModal(serviceData) {
                                document.getElementById("serviceNameWhitening").value = serviceData.service_name || 'Dental Whitening';
                                document.getElementById("serviceDescriptionWhitening").value = serviceData.service_description || '';
                                document.getElementById("partialPriceMinWhitening").value = serviceData.partial_price_min || '';
                                document.getElementById("partialPriceMaxWhitening").value = serviceData.partial_price_max || '';
                                document.getElementById("completePriceMinWhitening").value = serviceData.complete_price_min || '';
                                document.getElementById("completePriceMaxWhitening").value = serviceData.complete_price_max || '';
                                modalWhitening.style.display = "block";
                            }

                            // Function to reset modal
                            function resetModal(formId, previewId) {
                                document.getElementById(formId).reset();
                                document.getElementById(previewId).style.display = 'none';
                            }

                            // Close Modal
                            spanWhitening.onclick = function () {
                                resetModal('serviceFormWhitening', 'imagePreviewWhitening');
                                modalWhitening.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalWhitening) {
                                    resetModal('serviceFormWhitening', 'imagePreviewWhitening');
                                    modalWhitening.style.display = "none";
                                }
                            }

                            // Example: Open Modal for Dental Whitening
                            document.getElementById("openModalBtnWhitening").onclick = function () {
                                var whiteningData = <?php echo json_encode($whiteningData); ?>;
                                openServiceModal(whiteningData);
                            };
                        </script>
                    </div>
                </div>

                <!-- Img-box and Modal for Dental Implants -->
                <div class="img-box" id="openModalBtnImplants">
                    <div class="img-wrapper">
                        <p>
                            <?php echo htmlspecialchars($implantsData ? $implantsData['service_name'] : 'Dental Implants'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($implantsData ? basename($implantsData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($implantsData ? $implantsData['service_name'] : 'Dental Implants'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>

                <!-- Modal Template for Dental Implants -->
                <div id="serviceModalImplants" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Dental Implants</h2>
                        <form id="serviceFormImplants" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="serviceNameImplants">

                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputImplants" accept="image/*"
                                onchange="previewImage(event, 'imagePreviewImplants')"><br>
                            <img id="imagePreviewImplants" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />

                            <label>Description:</label>
                            <textarea name="service_description" id="serviceDescriptionImplants"
                                required></textarea><br>

                            <label>Estimated Price:</label>
                            <input type="number" name="partial_price_min" id="partialPriceMinImplants"
                                placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" id="partialPriceMaxImplants"
                                placeholder="Max Price" required><br>

                            <label>Total Amount:</label>
                            <input type="number" name="complete_price_min" id="completePriceMinImplants"
                                placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" id="completePriceMaxImplants"
                                placeholder="Max Price" required><br>

                            <button type="submit">Save Changes</button>
                        </form>

                        <script>
                            // General Modal Elements and Functions for Dental Implants
                            var modalImplants = document.getElementById("serviceModalImplants");
                            var spanImplants = document.getElementsByClassName("close")[3];

                            // Function to handle modal population and open
                            function openServiceModal(serviceData) {
                                document.getElementById("serviceNameImplants").value = serviceData.service_name || 'Dental Implants';
                                document.getElementById("serviceDescriptionImplants").value = serviceData.service_description || '';
                                document.getElementById("partialPriceMinImplants").value = serviceData.partial_price_min || '';
                                document.getElementById("partialPriceMaxImplants").value = serviceData.partial_price_max || '';
                                document.getElementById("completePriceMinImplants").value = serviceData.complete_price_min || '';
                                document.getElementById("completePriceMaxImplants").value = serviceData.complete_price_max || '';
                                modalImplants.style.display = "block";
                            }

                            // Function to reset modal
                            function resetModal(formId, previewId) {
                                document.getElementById(formId).reset();
                                document.getElementById(previewId).style.display = 'none';
                            }

                            // Close Modal
                            spanImplants.onclick = function () {
                                resetModal('serviceFormImplants', 'imagePreviewImplants');
                                modalImplants.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalImplants) {
                                    resetModal('serviceFormImplants', 'imagePreviewImplants');
                                    modalImplants.style.display = "none";
                                }
                            }

                            // Example: Open Modal for Dental Implants
                            document.getElementById("openModalBtnImplants").onclick = function () {
                                var implantsData = <?php echo json_encode($implantsData); ?>;
                                openServiceModal(implantsData);
                            };
                        </script>
                    </div>
                </div>

                <!-- Img-box and Modal for Restoration -->
                <div class="img-box" id="openModalBtnRestoration">
                    <div class="img-wrapper">
                        <p>
                            <?php echo htmlspecialchars($restorationData ? $restorationData['service_name'] : 'Restoration'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($restorationData ? basename($restorationData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($restorationData ? $restorationData['service_name'] : 'Restoration'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>

                <!-- Modal Template for Restoration -->
                <div id="serviceModalRestoration" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Restoration</h2>
                        <form id="serviceFormRestoration" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="serviceNameRestoration">

                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputRestoration" accept="image/*"
                                onchange="previewImage(event, 'imagePreviewRestoration')"><br>
                            <img id="imagePreviewRestoration" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />

                            <label>Description:</label>
                            <textarea name="service_description" id="serviceDescriptionRestoration"
                                required></textarea><br>

                            <label>Estimated Price:</label>
                            <input type="number" name="partial_price_min" id="partialPriceMinRestoration"
                                placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" id="partialPriceMaxRestoration"
                                placeholder="Max Price" required><br>

                            <label>Total Amount:</label>
                            <input type="number" name="complete_price_min" id="completePriceMinRestoration"
                                placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" id="completePriceMaxRestoration"
                                placeholder="Max Price" required><br>

                            <button type="submit">Save Changes</button>
                        </form>

                        <script>
                            // General Modal Elements and Functions for Restoration
                            var modalRestoration = document.getElementById("serviceModalRestoration");
                            var spanRestoration = document.getElementsByClassName("close")[4];

                            // Function to handle modal population and open
                            function openServiceModal(serviceData) {
                                document.getElementById("serviceNameRestoration").value = serviceData.service_name || 'Restoration';
                                document.getElementById("serviceDescriptionRestoration").value = serviceData.service_description || '';
                                document.getElementById("partialPriceMinRestoration").value = serviceData.partial_price_min || '';
                                document.getElementById("partialPriceMaxRestoration").value = serviceData.partial_price_max || '';
                                document.getElementById("completePriceMinRestoration").value = serviceData.complete_price_min || '';
                                document.getElementById("completePriceMaxRestoration").value = serviceData.complete_price_max || '';
                                modalRestoration.style.display = "block";
                            }

                            // Function to reset modal
                            function resetModal(formId, previewId) {
                                document.getElementById(formId).reset();
                                document.getElementById(previewId).style.display = 'none';
                            }

                            // Close Modal
                            spanRestoration.onclick = function () {
                                resetModal('serviceFormRestoration', 'imagePreviewRestoration');
                                modalRestoration.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalRestoration) {
                                    resetModal('serviceFormRestoration', 'imagePreviewRestoration');
                                    modalRestoration.style.display = "none";
                                }
                            }

                            // Example: Open Modal for Restoration
                            document.getElementById("openModalBtnRestoration").onclick = function () {
                                var restorationData = <?php echo json_encode($restorationData); ?>;
                                openServiceModal(restorationData);
                            };
                        </script>
                    </div>
                </div>

                <!-- Img-box and Modal for Extraction -->
                <div class="img-box" id="openModalBtnExtraction">
                    <div class="img-wrapper">
                        <p>
                            <?php echo htmlspecialchars($extractionData ? $extractionData['service_name'] : 'Extraction'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($extractionData ? basename($extractionData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($extractionData ? $extractionData['service_name'] : 'Extraction'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>

                <!-- Modal Template for Extraction -->
                <div id="serviceModalExtraction" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Extraction</h2>
                        <form id="serviceFormExtraction" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="serviceNameExtraction">

                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputExtraction" accept="image/*"
                                onchange="previewImage(event, 'imagePreviewExtraction')"><br>
                            <img id="imagePreviewExtraction" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />

                            <label>Description:</label>
                            <textarea name="service_description" id="serviceDescriptionExtraction"
                                required></textarea><br>

                            <label>Estimated Price:</label>
                            <input type="number" name="partial_price_min" id="partialPriceMinExtraction"
                                placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" id="partialPriceMaxExtraction"
                                placeholder="Max Price" required><br>

                            <label>Total Amount:</label>
                            <input type="number" name="complete_price_min" id="completePriceMinExtraction"
                                placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" id="completePriceMaxExtraction"
                                placeholder="Max Price" required><br>

                            <button type="submit">Save Changes</button>
                        </form>

                        <script>
                            // General Modal Elements and Functions for Extraction
                            var modalExtraction = document.getElementById("serviceModalExtraction");
                            var spanExtraction = document.getElementsByClassName("close")[5];

                            // Function to handle modal population and open
                            function openServiceModal(serviceData) {
                                document.getElementById("serviceNameExtraction").value = serviceData.service_name || 'Extraction';
                                document.getElementById("serviceDescriptionExtraction").value = serviceData.service_description || '';
                                document.getElementById("partialPriceMinExtraction").value = serviceData.partial_price_min || '';
                                document.getElementById("partialPriceMaxExtraction").value = serviceData.partial_price_max || '';
                                document.getElementById("completePriceMinExtraction").value = serviceData.complete_price_min || '';
                                document.getElementById("completePriceMaxExtraction").value = serviceData.complete_price_max || '';
                                modalExtraction.style.display = "block";
                            }

                            // Function to reset modal
                            function resetModal(formId, previewId) {
                                document.getElementById(formId).reset();
                                document.getElementById(previewId).style.display = 'none';
                            }

                            // Close Modal
                            spanExtraction.onclick = function () {
                                resetModal('serviceFormExtraction', 'imagePreviewExtraction');
                                modalExtraction.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalExtraction) {
                                    resetModal('serviceFormExtraction', 'imagePreviewExtraction');
                                    modalExtraction.style.display = "none";
                                }
                            }

                            // Example: Open Modal for Extraction
                            document.getElementById("openModalBtnExtraction").onclick = function () {
                                var extractionData = <?php echo json_encode($extractionData); ?>;
                                openServiceModal(extractionData);
                            };
                        </script>
                    </div>
                </div>

                <!-- Img-box and Modal for Veneers -->
                <div class="img-box" id="openModalBtnVeneers">
                    <div class="img-wrapper">
                        <p>
                            <?php echo htmlspecialchars($veneersData ? $veneersData['service_name'] : 'All Porcelain Veneers & Zirconia'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($veneersData ? basename($veneersData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($veneersData ? $veneersData['service_name'] : 'All Porcelain Veneers & Zirconia'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>

                <!-- Modal Template for Veneers -->
                <div id="serviceModalVeneers" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit All Porcelain Veneers & Zirconia</h2>
                        <form id="serviceFormVeneers" method="POST" action="services.php" enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="serviceNameVeneers">

                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputVeneers" accept="image/*"
                                onchange="previewImage(event, 'imagePreviewVeneers')"><br>
                            <img id="imagePreviewVeneers" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />

                            <label>Description:</label>
                            <textarea name="service_description" id="serviceDescriptionVeneers" required></textarea><br>

                            <label>Estimated Price:</label>
                            <input type="number" name="partial_price_min" id="partialPriceMinVeneers"
                                placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" id="partialPriceMaxVeneers"
                                placeholder="Max Price" required><br>

                            <label>Total Amount:</label>
                            <input type="number" name="complete_price_min" id="completePriceMinVeneers"
                                placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" id="completePriceMaxVeneers"
                                placeholder="Max Price" required><br>

                            <button type="submit">Save Changes</button>
                        </form>

                        <script>
                            // General Modal Elements and Functions for Veneers
                            var modalVeneers = document.getElementById("serviceModalVeneers");
                            var spanVeneers = document.getElementsByClassName("close")[6];

                            // Function to handle modal population and open
                            function openServiceModal(serviceData) {
                                document.getElementById("serviceNameVeneers").value = serviceData.service_name || 'All Porcelain Veneers & Zirconia';
                                document.getElementById("serviceDescriptionVeneers").value = serviceData.service_description || '';
                                document.getElementById("partialPriceMinVeneers").value = serviceData.partial_price_min || '';
                                document.getElementById("partialPriceMaxVeneers").value = serviceData.partial_price_max || '';
                                document.getElementById("completePriceMinVeneers").value = serviceData.complete_price_min || '';
                                document.getElementById("completePriceMaxVeneers").value = serviceData.complete_price_max || '';
                                modalVeneers.style.display = "block";
                            }

                            // Function to reset modal
                            function resetModal(formId, previewId) {
                                document.getElementById(formId).reset();
                                document.getElementById(previewId).style.display = 'none';
                            }

                            // Close Modal
                            spanVeneers.onclick = function () {
                                resetModal('serviceFormVeneers', 'imagePreviewVeneers');
                                modalVeneers.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalVeneers) {
                                    resetModal('serviceFormVeneers', 'imagePreviewVeneers');
                                    modalVeneers.style.display = "none";
                                }
                            }

                            // Example: Open Modal for Veneers
                            document.getElementById("openModalBtnVeneers").onclick = function () {
                                var veneersData = <?php echo json_encode($veneersData); ?>;
                                openServiceModal(veneersData);
                            };
                        </script>
                    </div>
                </div>

                <!-- Img-box and Modal for Full Exam & X-Ray -->
                <div class="img-box" id="openModalBtnExam">
                    <div class="img-wrapper">
                        <p>
                            <?php echo htmlspecialchars($examData ? $examData['service_name'] : 'Full Exam & X-Ray'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($examData ? basename($examData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($examData ? $examData['service_name'] : 'Full Exam & X-Ray'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>

                <!-- Modal Template for Full Exam & X-Ray -->
                <div id="serviceModalExam" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Full Exam & X-Ray</h2>
                        <form id="serviceFormExam" method="POST" action="services.php" enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="serviceNameExam">

                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputExam" accept="image/*"
                                onchange="previewImage(event, 'imagePreviewExam')"><br>
                            <img id="imagePreviewExam" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />

                            <label>Description:</label>
                            <textarea name="service_description" id="serviceDescriptionExam" required></textarea><br>

                            <label>Total Amount:</label>
                            <input type="number" name="complete_price_min" id="completePriceMinExam"
                                placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" id="completePriceMaxExam"
                                placeholder="Max Price" required><br>

                            <button type="submit">Save Changes</button>
                        </form>

                        <script>
                            // General Modal Elements and Functions for Full Exam & X-Ray
                            var modalExam = document.getElementById("serviceModalExam");
                            var spanExam = document.getElementsByClassName("close")[7];

                            // Function to handle modal population and open
                            function openServiceModal(serviceData) {
                                document.getElementById("serviceNameExam").value = serviceData.service_name || 'Full Exam & X-Ray';
                                document.getElementById("serviceDescriptionExam").value = serviceData.service_description || '';
                                document.getElementById("completePriceMinExam").value = serviceData.complete_price_min || '';
                                document.getElementById("completePriceMaxExam").value = serviceData.complete_price_max || '';
                                modalExam.style.display = "block";
                            }

                            // Function to reset modal
                            function resetModal(formId, previewId) {
                                document.getElementById(formId).reset();
                                document.getElementById(previewId).style.display = 'none';
                            }

                            // Close Modal
                            spanExam.onclick = function () {
                                resetModal('serviceFormExam', 'imagePreviewExam');
                                modalExam.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalExam) {
                                    resetModal('serviceFormExam', 'imagePreviewExam');
                                    modalExam.style.display = "none";
                                }
                            }

                            // Example: Open Modal for Full Exam & X-Ray
                            document.getElementById("openModalBtnExam").onclick = function () {
                                var examData = <?php echo json_encode($examData); ?>;
                                openServiceModal(examData);
                            };
                        </script>
                    </div>
                </div>

                <!-- Img-box and Modal for Root Canal Treatment -->
                <div class="img-box" id="openModalBtnRootCanal">
                    <div class="img-wrapper">
                        <p><?php echo htmlspecialchars($rootData ? $rootData['service_name'] : 'Root Canal Treatment'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($rootData ? basename($rootData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($rootData ? $rootData['service_name'] : 'Root Canal Treatment'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>

                <!-- Modal Template for Root Canal Treatment -->
                <div id="serviceModalRootCanal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Root Canal Treatment</h2>
                        <form id="serviceFormRootCanal" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="serviceNameRootCanal"
                                value="Root Canal Treatment">

                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputRootCanal" accept="image/*"
                                onchange="previewImage(event, 'imagePreviewRootCanal')"><br>
                            <img id="imagePreviewRootCanal" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />

                            <label>Description:</label>
                            <textarea name="service_description" id="serviceDescriptionRootCanal"
                                required>Enter service details here</textarea><br>

                            <label>Estimated Price:</label>
                            <input type="number" name="partial_price_min" id="partialPriceMinRootCanal"
                                placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" id="partialPriceMaxRootCanal"
                                placeholder="Max Price" required><br>

                            <label>Total Amount:</label>
                            <input type="number" name="complete_price_min" id="completePriceMinRootCanal"
                                placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" id="completePriceMaxRootCanal"
                                placeholder="Max Price" required><br>

                            <button type="submit">Save Changes</button>
                        </form>

                        <script>
                            // General Modal Elements and Functions for Root Canal Treatment
                            var modalRootCanal = document.getElementById("serviceModalRootCanal");
                            var spanRootCanal = document.getElementsByClassName("close")[8];

                            // Function to handle modal population and open
                            function openServiceModal(serviceData) {
                                document.getElementById("serviceNameRootCanal").value = serviceData.service_name || 'Root Canal Treatment';
                                document.getElementById("serviceDescriptionRootCanal").value = serviceData.service_description || '';
                                document.getElementById("partialPriceMinRootCanal").value = serviceData.partial_price_min || '';
                                document.getElementById("partialPriceMaxRootCanal").value = serviceData.partial_price_max || '';
                                document.getElementById("completePriceMinRootCanal").value = serviceData.complete_price_min || '';
                                document.getElementById("completePriceMaxRootCanal").value = serviceData.complete_price_max || '';
                                modalRootCanal.style.display = "block";
                            }

                            // Function to reset modal
                            function resetModal(formId, previewId) {
                                document.getElementById(formId).reset();
                                document.getElementById(previewId).style.display = 'none';
                            }

                            // Close Modal
                            spanRootCanal.onclick = function () {
                                resetModal('serviceFormRootCanal', 'imagePreviewRootCanal');
                                modalRootCanal.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalRootCanal) {
                                    resetModal('serviceFormRootCanal', 'imagePreviewRootCanal');
                                    modalRootCanal.style.display = "none";
                                }
                            }

                            // Example: Open Modal for Root Canal Treatment
                            document.getElementById("openModalBtnRootCanal").onclick = function () {
                                var rootData = <?php echo json_encode($rootData); ?>;
                                openServiceModal(rootData);
                            };
                        </script>
                    </div>
                </div>

                <!-- Img-box and Modal for Dentures -->
                <div class="img-box" id="openModalBtnDentures">
                    <div class="img-wrapper">
                        <p><?php echo htmlspecialchars($dentureData ? $dentureData['service_name'] : 'Dentures'); ?></p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($dentureData ? basename($dentureData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($dentureData ? $dentureData['service_name'] : 'Dentures'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>

                <!-- Modal Template for Dentures -->
                <div id="serviceModalDentures" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Dentures</h2>
                        <form id="serviceFormDentures" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="serviceNameDentures" value="Dentures">

                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputDentures" accept="image/*"
                                onchange="previewImage(event, 'imagePreviewDentures')"><br>
                            <img id="imagePreviewDentures" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />

                            <label>Description:</label>
                            <textarea name="service_description" id="serviceDescriptionDentures"
                                required>Enter service details here</textarea><br>

                            <label>Estimated Price:</label>
                            <input type="number" name="partial_price_min" id="partialPriceMinDentures"
                                placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" id="partialPriceMaxDentures"
                                placeholder="Max Price" required><br>

                            <label>Total Amount:</label>
                            <input type="number" name="complete_price_min" id="completePriceMinDentures"
                                placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" id="completePriceMaxDentures"
                                placeholder="Max Price" required><br>

                            <button type="submit">Save Changes</button>
                        </form>

                        <script>
                            // General Modal Elements and Functions for Dentures
                            var modalDentures = document.getElementById("serviceModalDentures");
                            var spanDentures = document.getElementsByClassName("close")[9];

                            // Function to handle modal population and open
                            function openServiceModal(serviceData) {
                                document.getElementById("serviceNameDentures").value = serviceData.service_name || 'Dentures';
                                document.getElementById("serviceDescriptionDentures").value = serviceData.service_description || '';
                                document.getElementById("partialPriceMinDentures").value = serviceData.partial_price_min || '';
                                document.getElementById("partialPriceMaxDentures").value = serviceData.partial_price_max || '';
                                document.getElementById("completePriceMinDentures").value = serviceData.complete_price_min || '';
                                document.getElementById("completePriceMaxDentures").value = serviceData.complete_price_max || '';
                                modalDentures.style.display = "block";
                            }

                            // Function to reset modal
                            function resetModal(formId, previewId) {
                                document.getElementById(formId).reset();
                                document.getElementById(previewId).style.display = 'none';
                            }

                            // Close Modal
                            spanDentures.onclick = function () {
                                resetModal('serviceFormDentures', 'imagePreviewDentures');
                                modalDentures.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalDentures) {
                                    resetModal('serviceFormDentures', 'imagePreviewDentures');
                                    modalDentures.style.display = "none";
                                }
                            }

                            // Example: Open Modal for Dentures
                            document.getElementById("openModalBtnDentures").onclick = function () {
                                var dentureData = <?php echo json_encode($dentureData); ?>;
                                openServiceModal(dentureData);
                            };
                        </script>
                    </div>
                </div>

                <!-- Img-box and Modal for Crown & Bridge -->
                <div class="img-box" id="openModalBtnCrownBridge">
                    <div class="img-wrapper">
                        <p><?php echo htmlspecialchars($crownBridgeData ? $crownBridgeData['service_name'] : 'Crown & Bridge'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($crownBridgeData ? basename($crownBridgeData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($crownBridgeData ? $crownBridgeData['service_name'] : 'Crown & Bridge'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>

                <!-- Modal Template for Crown & Bridge -->
                <div id="serviceModalCrownBridge" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Crown & Bridge</h2>
                        <form id="serviceFormCrownBridge" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="serviceNameCrownBridge" value="Crown & Bridge">

                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputCrownBridge" accept="image/*"
                                onchange="previewImage(event, 'imagePreviewCrownBridge')"><br>
                            <img id="imagePreviewCrownBridge" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />

                            <label>Description:</label>
                            <textarea name="service_description" id="serviceDescriptionCrownBridge"
                                required>Enter service details here</textarea><br>

                            <label>Estimated Price:</label>
                            <input type="number" name="partial_price_min" id="partialPriceMinCrownBridge"
                                placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" id="partialPriceMaxCrownBridge"
                                placeholder="Max Price" required><br>

                            <label>Total Amount:</label>
                            <input type="number" name="complete_price_min" id="completePriceMinCrownBridge"
                                placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" id="completePriceMaxCrownBridge"
                                placeholder="Max Price" required><br>

                            <button type="submit">Save Changes</button>
                        </form>

                        <script>
                            // General Modal Elements and Functions for Crown & Bridge
                            var modalCrownBridge = document.getElementById("serviceModalCrownBridge");
                            var spanCrownBridge = document.getElementsByClassName("close")[10];

                            // Function to handle modal population and open
                            function openServiceModal(serviceData) {
                                document.getElementById("serviceNameCrownBridge").value = serviceData.service_name || 'Crown & Bridge';
                                document.getElementById("serviceDescriptionCrownBridge").value = serviceData.service_description || '';
                                document.getElementById("partialPriceMinCrownBridge").value = serviceData.partial_price_min || '';
                                document.getElementById("partialPriceMaxCrownBridge").value = serviceData.partial_price_max || '';
                                document.getElementById("completePriceMinCrownBridge").value = serviceData.complete_price_min || '';
                                document.getElementById("completePriceMaxCrownBridge").value = serviceData.complete_price_max || '';
                                modalCrownBridge.style.display = "block";
                            }

                            // Function to reset modal
                            function resetModal(formId, previewId) {
                                document.getElementById(formId).reset();
                                document.getElementById(previewId).style.display = 'none';
                            }

                            // Close Modal
                            spanCrownBridge.onclick = function () {
                                resetModal('serviceFormCrownBridge', 'imagePreviewCrownBridge');
                                modalCrownBridge.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalCrownBridge) {
                                    resetModal('serviceFormCrownBridge', 'imagePreviewCrownBridge');
                                    modalCrownBridge.style.display = "none";
                                }
                            }

                            // Example: Open Modal for Crown & Bridge
                            document.getElementById("openModalBtnCrownBridge").onclick = function () {
                                var crownBridgeData = <?php echo json_encode($crownBridgeData); ?>;
                                openServiceModal(crownBridgeData);
                            };
                        </script>
                    </div>
                </div>

                <script>
                    var modal = document.getElementById("serviceModalCrownBridge");
                    var btn = document.getElementById("openModalBtn"); // Ensure this button exists in your HTML
                    var span = document.getElementsByClassName("close")[0];
                    var imagePreview = document.getElementById("imagePreviewCrownBridge"); // Updated to use the correct preview ID
                    var serviceForm = document.getElementById("serviceFormCrownBridge");

                    btn.onclick = function () {
                        modal.style.display = "block";
                    }

                    span.onclick = function () {
                        resetModal(); // Reset the modal when closed
                        modal.style.display = "none";
                    }

                    window.onclick = function (event) {
                        if (event.target == modal) {
                            resetModal(); // Reset the modal when closed
                            modal.style.display = "none";
                        }
                    }

                    function previewImage(event) {
                        imagePreview.style.display = "block";
                        imagePreview.src = URL.createObjectURL(event.target.files[0]);
                    }

                    function resetModal() {
                        serviceForm.reset(); // Reset the form fields
                        imagePreview.style.display = "none"; // Hide the image preview
                        imagePreview.src = ""; // Clear the image preview source
                    }
                </script>
            </div>
        </div>

    </div>
    </div>
</body>

</html>