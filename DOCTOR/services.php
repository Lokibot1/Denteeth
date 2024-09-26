<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'doctor', 'dental_assistant'])) {
    header("Location: ../login.php");
    exit();
}

include("../dbcon.php"); // Your database connection

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = $_POST['service_description'];
    $partial_price_min = $_POST['partial_price_min'];
    $partial_price_max = $_POST['partial_price_max'];
    $complete_price_min = $_POST['complete_price_min'];
    $complete_price_max = $_POST['complete_price_max'];
    $service_name = $_POST['service_name'] ?? ''; // This will retrieve the service name from the hidden input

    if (empty($service_name)) {
        echo "Service name is required.";
        exit();
    }

    // Handle image upload
    $target_dir = "C:/xampp/htdocs/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/"; // Absolute path to the target directory
    $target_file = $target_dir . basename($_FILES["service_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["service_image"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["service_image"]["size"] > 500000) { // Limit to 500KB
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // Create the target directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) {
            // If the image is uploaded successfully, retrieve service name
            $service_name = $_POST['service_name']; // Retrieve service name after image upload

            // Prepare and bind with switched parameters
            $stmt = $con->prepare("INSERT INTO services (service_name, service_image, service_description, partial_price_min, partial_price_max, complete_price_min, complete_price_max) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdddd", $target_file, $service_name, $description, $partial_price_min, $partial_price_max, $complete_price_min, $complete_price_max);

            // Execute the statement
            if ($stmt->execute()) {
                // Redirect to the same page to prevent resubmission
                header("Location: services.php");
                exit(); // Important to call exit after redirect
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Close connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="services.css">
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
            <a href="services.php" class="w3-bar-item w3-button active">Services</a>
            <a href="transaction_history.php" class="w3-bar-item w3-button">Transaction History</a>
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
            <h1>Services</h1>
            <div id="crvs-container">
                <!-- Img-box and Modal for Orthodontic Braces -->
                <div class="img-box" id="openModalBtnBraces">
                    <div class="img-wrapper">
                        <p>ORTHODONTIC BRACES</p>
                        <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                    </div>
                </div>
                <div id="serviceModalBraces" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Orthodontic Braces</h2>
                        <form id="serviceFormBraces" method="POST" action="services.php" enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="service_name_braces">

                            <label>Image Upload:</label>
                            <input type="file" name="service_image" id="imageInputBraces" accept="image/*" required
                                onchange="previewImage(event, 'imagePreviewBraces')"><br>
                            <img id="imagePreviewBraces" src="" alt="Image Preview"
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
                            var modalBraces = document.getElementById("serviceModalBraces");
                            var btnBraces = document.getElementById("openModalBtnBraces");
                            var spanBraces = document.getElementsByClassName("close")[0];

                            btnBraces.onclick = function () {
                                document.getElementById("service_name_braces").value = "Orthodontic Braces"; 
                                modalBraces.style.display = "block";
                            }

                            spanBraces.onclick = function () {
                                resetModal('serviceFormBraces', 'imagePreviewBraces');
                                modalBraces.style.display = "none";
                            }

                            window.onclick = function (event) {
                                if (event.target == modalBraces) {
                                    resetModal('serviceFormBraces', 'imagePreviewBraces');
                                    modalBraces.style.display = "none";
                                }
                            }
                        </script>
                    </div>
                </div>

                <!-- Img-box and Modal for Dental Cleaning -->
                <div class="img-box" id="openModalBtnCleaning">
                    <div class="img-wrapper">
                        <p>DENTAL CLEANING</p>
                        <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                    </div>
                </div>
                <div id="serviceModalCleaning" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Dental Cleaning</h2>
                        <form id="serviceFormCleaning" method="POST" action="services.php" enctype="multipart/form-data">
                            <input type="hidden" name="service_name" id="service_name_dental_cleaning">
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
                                document.getElementById("service_name_dental_cleaning").value = "Dental Cleaning";
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
                        <p>DENTAL WHITENING</p>
                        <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                    </div>
                </div>
                <div id="serviceModalWhitening" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Dental Whitening</h2>
                        <form id="serviceFormWhitening" method="POST" action="services.php"
                            enctype="multipart/form-data">
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
                        <p>DENTAL IMPLANTS</p>
                        <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                    </div>
                </div>
                <div id="serviceModalImplants" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Dental Implants</h2>
                        <form id="serviceFormImplants" method="POST" action="services.php"
                            enctype="multipart/form-data">
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
                        <p>RESTORATION</p>
                        <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                    </div>
                </div>
                <div id="serviceModalRestoration" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Restoration</h2>
                        <form id="serviceFormRestoration" method="POST" action="services.php"
                            enctype="multipart/form-data">
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

                <!-- Img-box and Modal for Extraction -->
                <div class="img-box" id="openModalBtnExtraction">
                    <div class="img-wrapper">
                        <p>EXTRACTION</p>
                        <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                    </div>
                </div>
                <div id="serviceModalExtraction" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Extraction</h2>
                        <form id="serviceFormExtraction" method="POST" action="services.php"
                            enctype="multipart/form-data">
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

                <!-- Img-box and Modal for All Porcelain Veneers & Zirconia -->
                <div class="img-box" id="openModalBtnVeneers">
                    <div class="img-wrapper">
                        <p>ALL PORCELAIN VENEERS & ZIRCONIA</p>
                        <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                    </div>
                </div>
                <div id="serviceModalVeneers" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit All Porcelain Veneers & Zirconia</h2>
                        <form id="serviceFormVeneers" method="POST" action="services.php" enctype="multipart/form-data">
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

                <!-- Img-box and Modal for Full Exam & X-Ray -->
                <div class="img-box" id="openModalBtnExam">
                    <div class="img-wrapper">
                        <p>FULL EXAM & X-RAY</p>
                        <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                    </div>
                </div>
                <div id="serviceModalExam" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Full Exam & X-Ray</h2>
                        <form id="serviceFormExam" method="POST" action="services.php" enctype="multipart/form-data">
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

                <!-- Img-box and Modal for Root Canal Treatment -->
                <div class="img-box" id="openModalBtnRootCanal">
                    <div class="img-wrapper">
                        <p>ROOT CANAL TREATMENT</p>
                        <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                    </div>
                </div>
                <div id="serviceModalRootCanal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Root Canal Treatment</h2>
                        <form id="serviceFormRootCanal" method="POST" action="services.php"
                            enctype="multipart/form-data">
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

                <!-- Img-box and Modal for Dentures -->
                <div class="img-box" id="openModalBtnDentures">
                    <div class="img-wrapper">
                        <p>DENTURES</p>
                        <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                    </div>
                </div>
                <div id="serviceModalDentures" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Dentures</h2>
                        <form id="serviceFormDentures" method="POST" action="services.php"
                            enctype="multipart/form-data">
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

                <!-- Img-box and Modal for Crown & Bridge -->
                <div class="img-box" id="openModalBtnCrownBridge">
                    <div class="img-wrapper">
                        <p>CROWN & BRIDGE</p>
                        <img src="https://i.pinimg.com/736x/d5/cb/a4/d5cba4e860f88132e33a6875af1f2eee.jpg" alt="">
                    </div>
                </div>
                <div id="serviceModalCrownBridge" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Crown & Bridge</h2>
                        <form id="serviceFormCrownBridge" method="POST" action="services.php"
                            enctype="multipart/form-data">
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