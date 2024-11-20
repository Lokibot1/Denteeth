<?php
// Database connection (adjust connection settings as needed)
include("../../dbcon.php");

// Check connection
if ($con->connect_error) {
  die("Connection failed: " . $con->connect_error);
}

// Fetch the service name from GET or POST (sanitize the input)
$service_name = isset($_GET['service_name']) ? htmlspecialchars($_GET['service_name']) : 'Extraction';

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
  $price = number_format($row['price'], 2);
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
      header("Location: Extraction.php");
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
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="master.css">
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
        - Per Unit ₱ <?php echo $price; ?>
      </h2>
      <!-- Button to open the modal -->
      <button id="openModal" class="button" onclick="openModal()">Open Booking Form</button>

      <!-- Edit Modal -->
      <div id="myModal" class="modal">
        <div class="modal-content">
          <span class="close" onclick="closeModal()">&times;</span>
          <form method="POST" action="">
            <h1>Booking Details</h1>
            <label for="modalt-name">Full Name: <br> (Last Name, First Name, Middle Initial)</label>
            <div class="name-fields">
              <input type="text" name="last_name" id="modal-last-name" placeholder="Enter Last Name" required>
              <input type="text" name="first_name" id="modal-first-name" placeholder="Enter First Name" required>
              <input type="text" name="middle_name" id="modal-middle-name" placeholder="Enter Middle Initial" required>
            </div>
            <label for="contact">Contact:</label>
            <input type="text" name="contact" id="modal-contact" placeholder="Enter your contact number" maxlength="11"
              required pattern="\d{11}" title="Please enter exactly 11 digits"><br>

            <label for="date">Date:</label>
            <input type="date" name="date" id="modal-date" required><br>

            <label for="time">Time: <br> (Will only accept appointments from 9:00 a.m to 6:00 p.m)</label>
            <select name="modified_time" id="modal-modified_time" required>
              <option value="09:00 AM">09:00 AM</option>
              <option value="10:30 AM">10:30 AM</option>
              <option value="11:00 AM" disabled>11:30 AM (Lunch Break)</option>
              <option value="12:00 PM">12:00 PM</option>
              <option value="01:30 PM">01:30 PM</option>
              <option value="03:00 PM">03:00 PM</option>
              <option value="04:30 PM">04:30 PM</option>
            </select>

            <label for="service_type">Type Of Service:</label>
            <input type="text" id="modal-service_type_display" value="Extraction" disabled>
            <input type="hidden" name="service_type" id="modal-service_type" value="7">
            <br>
            <input type="submit" name="update" value="Save">
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Open the modal and set date restrictions for the current week
    function openModal() {
      // Get today's date
      const today = new Date();

      // Calculate the start (today) and end (six days from today) of the current week
      const firstDay = new Date(today);
      const lastDay = new Date(firstDay);
      lastDay.setDate(firstDay.getDate() + 6);

      // Set min and max for the date input
      document.getElementById('modal-date').setAttribute('min', formatDate(firstDay));
      document.getElementById('modal-date').setAttribute('max', formatDate(lastDay));

      // Open the modal
      document.getElementById('myModal').style.display = 'block';
    }

    // Close the modal
    function closeModal() {
      document.getElementById('myModal').style.display = 'none';
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
      if (event.target == document.getElementById('myModal')) {
        closeModal();
      }
    }
  </script>
</body>

</html>