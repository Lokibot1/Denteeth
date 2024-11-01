<?php
// Database connection (adjust connection settings as needed)
include("../../dbcon.php");

// Check connection
if ($con->connect_error) {
  die("Connection failed: " . $con->connect_error);
}

// Fetch the service name from GET or POST (sanitize the input)
$service_name = isset($_GET['service_name']) ? htmlspecialchars($_GET['service_name']) : 'Root Canal Treatment';

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

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  // Get form data from modal
  $fname = mysqli_real_escape_string($con, $_POST['fname']);
  $contact = mysqli_real_escape_string($con, $_POST['contact']);
  $date = mysqli_real_escape_string($con, $_POST['date']);
  $time = mysqli_real_escape_string($con, $_POST['time']);
  $service_type = mysqli_real_escape_string($con, $_POST['service_type']);

  // Prepare the insert query
  $insert_query = "INSERT INTO tbl_appointments (fname, contact, date, time, service_type) VALUES (?, ?, ?, ?, ?)";

  $insert_stmt = $con->prepare($insert_query);
  if (!$insert_stmt) {
    echo "Error preparing insert statement: " . $con->error;
    exit;
  }

  // Bind parameters and execute the insert query
  $insert_stmt->bind_param("sssss", $fname, $contact, $date, $time, $service_type);
  if ($insert_stmt->execute()) {
    // Redirect to the same page after inserting
    header("Location: ../Home_page.php");
    exit();
  } else {
    echo "Error inserting record: " . $con->error;
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
        - Partial dentures: PHP <?php echo $partial_price_min; ?> - PHP <?php echo $partial_price_max; ?>
        <br><br>
        - Complete dentures: PHP <?php echo $complete_price_min; ?> - PHP <?php echo $complete_price_max; ?>
      </h2>
      <!-- Button to open the modal -->
      <button id="openModal" class="button">Open Booking Form</button>

      <!-- The Modal -->
      <div id="myModal" class="modal">
        <div class="modal-content">
          <span class="close">&times;</span>
          <label for="modalt-name">Full Name: <br> (Last Name, First Name, Middle Name)</label>
            <div class="name-fields">
            <input type="text" name="last_name" id="modal-last-name" placeholder="Enter Last Name" required>
            <input type="text" name="first_name" id="modal-first-name" placeholder="Enter First Name" required>
            <input type="text" name="middle_name" id="modal-middle-name" placeholder="Enter Middle Name" required>
            </div>
            <label for="contact">Contact:</label>
            <input type="text" name="contact" id="modal-contact" placeholder="Enter your contact number"
                  required><br>
            <label for="date">Date:</label>
                <input type="date" name="date" id="modal-date" required><br>
                <label for="time">Time: <br> (Will only accept appointments from 9:00 a.m to 6:00 p.m)</label>
                <input type="time" name="time" id="modal-time" required><br>
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
                </select><br><br>
                <input type="submit" name="update" value="BOOK">
            </form>
            <script>
              // Set min and max date for current week
              window.onload = function () {
                const today = new Date();
                const dayOfWeek = today.getDay();

                // Calculate the start (Monday) and end (Sunday) of the current week
                const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // Adjust if today is Sunday (day 0)
                const firstDay = new Date(today.setDate(today.getDate() + mondayOffset)); // Start of the week (Monday)
                const lastDay = new Date(firstDay);
                lastDay.setDate(firstDay.getDate() + 6); // End of the week (Sunday)

                // Set min and max for the date input
                document.getElementById('modal-date').setAttribute('min', formatDate(firstDay));
                document.getElementById('modal-date').setAttribute('max', formatDate(lastDay));
              };

              // Format date as YYYY-MM-DD
              function formatDate(date) {
                const year = date.getFullYear();
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const day = date.getDate().toString().padStart(2, '0');
                return `${year}-${month}-${day}`;
              }
            </script>
          </div>
        </div>
      </div>

      <script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Get the button that opens the modal
        var btn = document.getElementById("openModal");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks the button, open the modal
        btn.onclick = function () {
          modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function () {
          modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
          if (event.target == modal) {
            modal.style.display = "none";
          }
        }
      </script>

    </div>
  </div>
</body>

</html>