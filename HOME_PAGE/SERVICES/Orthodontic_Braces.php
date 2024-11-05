<?php
// Database connection (adjust connection settings as needed)
include("../../dbcon.php");

// Check connection
if ($con->connect_error) {
  die("Connection failed: " . $con->connect_error);
}

// Fetch the service name from GET or POST (sanitize the input)
$service_name = isset($_GET['service_name']) ? htmlspecialchars($_GET['service_name']) : 'Orthodontic Braces';

// Prepare the SQL query to fetch the service by name
$sql = "SELECT * FROM tbl_services WHERE service_name = ?";
$stmt = $con->prepare($sql);
if (!$stmt) {
  echo "Error preparing statement: " . $con->error;
  exit;
}

$stmt->bind_param("s", $service_name);
$stmt->execute();
$result = $stmt->get_result();

// Check if a row was found
if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $service_description = $row['service_description'];
  $partial_price_min = number_format($row['partial_price_min'], 2);
  $partial_price_max = number_format($row['partial_price_max'], 2);
  $complete_price_min = number_format($row['complete_price_min'], 2);
  $complete_price_max = number_format($row['complete_price_max'], 2);
  $service_image = !empty($row['service_image']) ? basename($row['service_image']) : 'default.jpg'; // Use default if image not found
} else {
  echo "Service not found.";
  exit;
}

if (isset($_POST['update'])) {
  // Check if 'id' is set in POST data to avoid undefined array key warning
  $id = isset($_POST['id']) ? $_POST['id'] : ''; // Set a default value if 'id' is not present
  $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
  $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
  $middle_name = mysqli_real_escape_string($con, $_POST['middle_name']);
  $contact = mysqli_real_escape_string($con, $_POST['contact']);
  $date = mysqli_real_escape_string($con, $_POST['date']);
  $time = mysqli_real_escape_string($con, $_POST['time']);
  $service_type = mysqli_real_escape_string($con, $_POST['service_type']);

  // Insert patient information
  $insert_patient_query = "INSERT INTO tbl_patient (first_name, last_name, middle_name) VALUES ('$first_name', '$last_name', '$middle_name')";

  if (mysqli_query($con, $insert_patient_query)) {
    // Get the ID of the newly inserted patient
    $patient_id = mysqli_insert_id($con);

    // Insert appointment information using the patient's ID as the name reference
    $insert_appointment_query = "INSERT INTO tbl_appointments (id, name, contact, date, time, service_type) VALUES ('$patient_id', '$patient_id', '$contact', '$date', '$time', '$service_type')";

    if (mysqli_query($con, $insert_appointment_query)) {
      // Redirect to the same page after inserting
      header("Location: Orthodontic_Braces.php");
      exit();
    } else {
      echo "Error updating appointment record: " . mysqli_error($con);
    }
  } else {
    echo "Error updating patient record: " . mysqli_error($con);
  }
}

$stmt->close();
$con->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="services.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">
  <title>Services</title>
  <style>

  </style>
</head>

<body>
  <nav>
    <a href="../Home_page.php#Services">
      <div class="logo">
        <h1>EHM Dental Clinic</h1>
      </div>
    </a>
  </nav>
  <div class="img-container">
    <!-- Display the dynamically fetched image -->
    <img src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars(basename($service_image)); ?>"
      alt="<?php echo htmlspecialchars($service_name); ?>"
      onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
  </div>

  <div class="container">
    <h1><?php echo htmlspecialchars($service_name); ?></h1>
    <p><?php echo htmlspecialchars($service_description); ?></p>

    <div class="price-item">
      <h2>
        - Partial dentures: ₱ <?php echo $partial_price_min; ?> - ₱ <?php echo $partial_price_max; ?><br><br>
        - Complete dentures: ₱ <?php echo $complete_price_min; ?> - ₱ <?php echo $complete_price_max; ?>
      </h2>
      <!-- Button to open the modal -->
      <button id="openModal" class="button" onclick="openModal()">Open Booking Form</button>

      <!-- Edit Modal -->
      <div id="myModal" class="modal">
        <div class="modal-content">
          <span class="close" onclick="closeModal()">&times;</span>
          <form method="POST" action="">
            <h1>Booking Details</h1><br>
            <label for="modal-first-name">First Name:</label>
            <input type="text" name="first_name" id="modal-first-name" required><br>

            <label for="modal-last-name">Last Name:</label>
            <input type="text" name="last_name" id="modal-last-name" required><br>

            <label for="modal-middle-name">Middle Name:</label>
            <input type="text" name="middle_name" id="modal-middle-name" required><br>

            <label for="contact">Contact:</label>
            <input type="text" name="contact" id="modal-contact" required><br>

            <label for="date">Date:</label>
            <input type="date" name="date" id="modal-date" required><br>

            <label for="time">Time:</label>
            <input type="time" name="time" id="modal-time" min="09:00" max="18:00" required>
            <p>CLINIC HOURS 9:00 AM TO 6:00 PM</p>

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
            </select><br>
            <input type="submit" name="update" value="Save">
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Open the modal without any parameters
    function openModal() {
      document.getElementById('myModal').style.display = 'block';
    }

    // Close the modal
    function closeModal() {
      document.getElementById('myModal').style.display = 'none';
    }

    // Close modal when clicking outside of it
    window.onclick = function (event) {
      if (event.target == document.getElementById('myModal')) {
        closeModal();
      }
    }
  </script>
</body>

</html>