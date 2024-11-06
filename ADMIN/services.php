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
    $description = $_POST['service_description'];
    $partial_price_min = $_POST['partial_price_min'];
    $partial_price_max = $_POST['partial_price_max'];
    $complete_price_min = $_POST['complete_price_min'];
    $complete_price_max = $_POST['complete_price_max'];
    $service_name = $_POST['service_name'] ?? ''; // Retrieve service name

    if (empty($service_name)) {
        echo "Service name is required.";
        exit();
    }

    // Handle image upload
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
    } else {
        // Create the target directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) {
            // Prepare and bind an update statement
            $stmt = $con->prepare("UPDATE tbl_services SET service_image = ?, service_description = ?, partial_price_min = ?, partial_price_max = ?, complete_price_min = ?, complete_price_max = ? WHERE service_name = ?");
            $stmt->bind_param("ssdddss", $target_file, $description, $partial_price_min, $partial_price_max, $complete_price_min, $complete_price_max, $service_name);

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
            echo "Sorry, there was an error uploading your file.";
        }
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
                <a href="admin_dashboard.php">
                    <h3>ADMIN<br>DASHBOARD</h3>
                </a>
                <br>
                <br>
                <hr>
                <br>
                <li><a href="pending.php">Pending Appointments</a></a></li>
                <li><a href="day.php">Appointment for the day</a></li>
                <li><a href="week.php">Appointment for the week</a></li>
                <li><a href="declined.php">Decline Appointments</a></a></li>
                <li><a href="finished.php">Finished Appointments</a></li>
                <li><a class="active" href="services.php">Services</a></li>
                <li><a href="manage_user.php">Manage Users</a></li>
                <li><a href="transaction_history.php">Transaction History</a></li>
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

                echo $finished_appointments ? $finished_appointments : 'No data available';
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
            <h1>Services</h1>
            <div id="crvs-container">
                <!-- Img-box and Modal for Orthodontic Braces -->
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
                <div id="serviceModalOrthodonticBraces" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Orthodontic Braces</h2>
                        <form id="serviceFormOrthodonticBraces" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" value="Orthodontic Braces">
                            <!-- Add this hidden input -->
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputOrthodonticBraces" accept="image/*"
                                required onchange="previewImage(event, 'imagePreviewOrthodonticBraces')"><br>
                            <img id="imagePreviewOrthodonticBraces" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" required>Enter service details here</textarea><br>
                            <label>Partial Price Range:</label>
                            <input type="number" name="partial_price_min" placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" placeholder="Max Price" required><br>
                            <label>Complete Price Range:</label>
                            <input type="number" name="complete_price_min" placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            var modalOrthodonticBraces = document.getElementById("serviceModalOrthodonticBraces");
                            var btnOrthodonticBraces = document.getElementById("openModalBtnOrthodonticBraces");
                            var spanOrthodonticBraces = document.getElementsByClassName("close")[0];

                            btnOrthodonticBraces.onclick = function () {
                                modalOrthodonticBraces.style.display = "block";
                            }

                            spanOrthodonticBraces.onclick = function () {
                                resetModal('serviceFormOrthodonticBraces', 'imagePreviewOrthodonticBraces');
                                modalOrthodonticBraces.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalOrthodonticBraces) {
                                    resetModal('serviceFormOrthodonticBraces', 'imagePreviewOrthodonticBraces');
                                    modalOrthodonticBraces.style.display = "none";
                                }
                            }
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
                            <input type="hidden" name="service_name" value="Dental Cleaning">
                            <!-- Add this hidden input -->
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputCleaning" accept="image/*" required
                                onchange="previewImage(event, 'imagePreviewCleaning')"><br>
                            <img id="imagePreviewCleaning" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" required>Enter service details here</textarea><br>
                            <label>Partial Price Range:</label>
                            <input type="number" name="partial_price_min" placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" placeholder="Max Price" required><br>
                            <label>Complete Price Range:</label>
                            <input type="number" name="complete_price_min" placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            var modalCleaning = document.getElementById("serviceModalCleaning");
                            var btnCleaning = document.getElementById("openModalBtnCleaning");
                            var spanCleaning = document.getElementsByClassName("close")[1];

                            btnCleaning.onclick = function () {
                                modalCleaning.style.display = "block";
                            }

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
                <div id="serviceModalWhitening" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Dental Whitening</h2>
                        <form id="serviceFormWhitening" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" value="Dental Whitening">
                            <!-- Add this hidden input -->
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputWhitening" accept="image/*" required
                                onchange="previewImage(event, 'imagePreviewWhitening')"><br>
                            <img id="imagePreviewWhitening" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" required>Enter service details here</textarea><br>
                            <label>Partial Price Range:</label>
                            <input type="number" name="partial_price_min" placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" placeholder="Max Price" required><br>
                            <label>Complete Price Range:</label>
                            <input type="number" name="complete_price_min" placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            var modalWhitening = document.getElementById("serviceModalWhitening");
                            var btnWhitening = document.getElementById("openModalBtnWhitening");
                            var spanWhitening = document.getElementsByClassName("close")[2];

                            btnWhitening.onclick = function () {
                                modalWhitening.style.display = "block";
                            }

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
                <div id="serviceModalImplants" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Dental Implants</h2>
                        <form id="serviceFormImplants" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" value="Dental Implants">
                            <!-- Add this hidden input -->
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputImplants" accept="image/*" required
                                onchange="previewImage(event, 'imagePreviewImplants')"><br>
                            <img id="imagePreviewImplants" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" required>Enter service details here</textarea><br>
                            <label>Partial Price Range:</label>
                            <input type="number" name="partial_price_min" placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" placeholder="Max Price" required><br>
                            <label>Complete Price Range:</label>
                            <input type="number" name="complete_price_min" placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            var modalImplants = document.getElementById("serviceModalImplants");
                            var btnImplants = document.getElementById("openModalBtnImplants");
                            var spanImplants = document.getElementsByClassName("close")[3];

                            btnImplants.onclick = function () {
                                modalImplants.style.display = "block";
                            }

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
                <div id="serviceModalRestoration" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Restoration</h2>
                        <form id="serviceFormRestoration" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" value="Restoration">
                            <!-- Add this hidden input -->
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputRestoration" accept="image/*" required
                                onchange="previewImage(event, 'imagePreviewRestoration')"><br>
                            <img id="imagePreviewRestoration" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" required>Enter service details here</textarea><br>
                            <label>Partial Price Range:</label>
                            <input type="number" name="partial_price_min" placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" placeholder="Max Price" required><br>
                            <label>Complete Price Range:</label>
                            <input type="number" name="complete_price_min" placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            var modalRestoration = document.getElementById("serviceModalRestoration");
                            var btnRestoration = document.getElementById("openModalBtnRestoration");
                            var spanRestoration = document.getElementsByClassName("close")[4];

                            btnRestoration.onclick = function () {
                                modalRestoration.style.display = "block";
                            }

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
                        </script>
                    </div>
                </div>
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
                <div id="serviceModalExtraction" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Extraction</h2>
                        <form id="serviceFormExtraction" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" value="Extraction">
                            <!-- Add this hidden input -->
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputExtraction" accept="image/*" required
                                onchange="previewImage(event, 'imagePreviewExtraction')"><br>
                            <img id="imagePreviewExtraction" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" required>Enter service details here</textarea><br>
                            <label>Partial Price Range:</label>
                            <input type="number" name="partial_price_min" placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" placeholder="Max Price" required><br>
                            <label>Complete Price Range:</label>
                            <input type="number" name="complete_price_min" placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            var modalExtraction = document.getElementById("serviceModalExtraction");
                            var btnExtraction = document.getElementById("openModalBtnExtraction");
                            var spanExtraction = document.getElementsByClassName("close")[5];

                            btnExtraction.onclick = function () {
                                modalExtraction.style.display = "block";
                            }

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
                        </script>
                    </div>
                </div>
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
                <div id="serviceModalVeneers" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit All Porcelain Veneers & Zirconia</h2>
                        <form id="serviceFormVeneers" method="POST" action="services.php" enctype="multipart/form-data">
                            <input type="hidden" name="service_name" value="All Porcelain Veneers & Zirconia">
                            <!-- Add this hidden input -->
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputVeneers" accept="image/*" required
                                onchange="previewImage(event, 'imagePreviewVeneers')"><br>
                            <img id="imagePreviewVeneers" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" required>Enter service details here</textarea><br>
                            <label>Partial Price Range:</label>
                            <input type="number" name="partial_price_min" placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" placeholder="Max Price" required><br>
                            <label>Complete Price Range:</label>
                            <input type="number" name="complete_price_min" placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            var modalVeneers = document.getElementById("serviceModalVeneers");
                            var btnVeneers = document.getElementById("openModalBtnVeneers");
                            var spanVeneers = document.getElementsByClassName("close")[6];

                            btnVeneers.onclick = function () {
                                modalVeneers.style.display = "block";
                            }

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
                        </script>
                    </div>
                </div>
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
                <div id="serviceModalExam" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Full Exam & X-Ray</h2>
                        <form id="serviceFormExam" method="POST" action="services.php" enctype="multipart/form-data">
                            <input type="hidden" name="service_name" value="Full Exam & X-Ray">
                            <!-- Add this hidden input -->
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputExam" accept="image/*" required
                                onchange="previewImage(event, 'imagePreviewExam')"><br>
                            <img id="imagePreviewExam" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" required>Enter service details here</textarea><br>
                            <label>Partial Price Range:</label>
                            <input type="number" name="partial_price_min" placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" placeholder="Max Price" required><br>
                            <label>Complete Price Range:</label>
                            <input type="number" name="complete_price_min" placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            var modalExam = document.getElementById("serviceModalExam");
                            var btnExam = document.getElementById("openModalBtnExam");
                            var spanExam = document.getElementsByClassName("close")[7];

                            btnExam.onclick = function () {
                                modalExam.style.display = "block";
                            }

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
                        </script>
                    </div>
                </div>
                <div class="img-box" id="openModalBtnRootCanal">
                    <div class="img-wrapper">
                        <p><?php echo htmlspecialchars($rootData ? $rootData['service_name'] : 'Root Canal Treatment'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($rootData ? basename($rootData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($rootData ? $rootData['service_name'] : 'Root Canal Treatment'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>
                <div id="serviceModalRootCanal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Root Canal Treatment</h2>
                        <form id="serviceFormRootCanal" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" value="Root Canal Treatment">
                            <!-- Add this hidden input -->
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputRootCanal" accept="image/*" required
                                onchange="previewImage(event, 'imagePreviewRootCanal')"><br>
                            <img id="imagePreviewRootCanal" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" required>Enter service details here</textarea><br>
                            <label>Partial Price Range:</label>
                            <input type="number" name="partial_price_min" placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" placeholder="Max Price" required><br>
                            <label>Complete Price Range:</label>
                            <input type="number" name="complete_price_min" placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            var modalRootCanal = document.getElementById("serviceModalRootCanal");
                            var btnRootCanal = document.getElementById("openModalBtnRootCanal");
                            var spanRootCanal = document.getElementsByClassName("close")[8];

                            btnRootCanal.onclick = function () {
                                modalRootCanal.style.display = "block";
                            }

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
                        </script>
                    </div>
                </div>
                <div class="img-box" id="openModalBtnDentures">
                    <div class="img-wrapper">
                        <p><?php echo htmlspecialchars($dentureData ? $dentureData['service_name'] : 'Dentures'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($dentureData ? basename($dentureData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($dentureData ? $dentureData['service_name'] : 'Dentures'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>
                <div id="serviceModalDentures" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Dentures</h2>
                        <form id="serviceFormDentures" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" value="Dentures">
                            <!-- Add this hidden input -->
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputDentures" accept="image/*" required
                                onchange="previewImage(event, 'imagePreviewDentures')"><br>
                            <img id="imagePreviewDentures" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" required>Enter service details here</textarea><br>
                            <label>Partial Price Range:</label>
                            <input type="number" name="partial_price_min" placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" placeholder="Max Price" required><br>
                            <label>Complete Price Range:</label>
                            <input type="number" name="complete_price_min" placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            var modalDentures = document.getElementById("serviceModalDentures");
                            var btnDentures = document.getElementById("openModalBtnDentures");
                            var spanDentures = document.getElementsByClassName("close")[9];

                            btnDentures.onclick = function () {
                                modalDentures.style.display = "block";
                            }

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
                        </script>
                    </div>
                </div>
                <div class="img-box" id="openModalBtnCrownBridge">
                    <div class="img-wrapper">
                        <p><?php echo htmlspecialchars($crownBridgeData ? $crownBridgeData['service_name'] : 'Crown & Bridge'); ?>
                        </p>
                        <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($crownBridgeData ? basename($crownBridgeData['service_image']) : 'default.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($crownBridgeData ? $crownBridgeData['service_name'] : 'Crown & Bridge'); ?>"
                            onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
                    </div>
                </div>
                <div id="serviceModalCrownBridge" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Crown & Bridge</h2>
                        <form id="serviceFormCrownBridge" method="POST" action="services.php"
                            enctype="multipart/form-data">
                            <input type="hidden" name="service_name" value="Crown & Bridge">
                            <!-- Add this hidden input -->
                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputCrownBridge" accept="image/*" required
                                onchange="previewImage(event, 'imagePreviewCrownBridge')"><br>
                            <img id="imagePreviewCrownBridge" src="" alt="Image Preview"
                                style="display: none; width: 200px; margin-top: 10px;" />
                            <label>Description:</label>
                            <textarea name="service_description" required>Enter service details here</textarea><br>
                            <label>Partial Price Range:</label>
                            <input type="number" name="partial_price_min" placeholder="Min Price" required>
                            <input type="number" name="partial_price_max" placeholder="Max Price" required><br>
                            <label>Complete Price Range:</label>
                            <input type="number" name="complete_price_min" placeholder="Min Price" required>
                            <input type="number" name="complete_price_max" placeholder="Max Price" required><br>
                            <button type="submit">Save Changes</button>
                        </form>
                        <script>
                            var modalCrownBridge = document.getElementById("serviceModalCrownBridge");
                            var btnCrownBridge = document.getElementById("openModalBtnCrownBridge");
                            var spanCrownBridge = document.getElementsByClassName("close")[10];

                            btnCrownBridge.onclick = function () {
                                modalCrownBridge.style.display = "block";
                            }

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
                        </script>
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