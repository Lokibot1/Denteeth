<?php
session_start();

include("../dbcon.php");

// Check database connection
if (!$con) {
  die("Connection failed: " . mysqli_connect_error());
}

// Prepare the SQL query to fetch the service by name
function fetchService($con, $service_name)
{
  $sql = "SELECT * FROM services WHERE service_name = ?";
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

// Handle insert request
if (isset($_POST['update'])) {
  // Get form data from modal
  $fname = mysqli_real_escape_string($con, $_POST['fname']);
  $contact = mysqli_real_escape_string($con, $_POST['contact']);
  $date = mysqli_real_escape_string($con, $_POST['date']);
  $time = mysqli_real_escape_string($con, $_POST['time']);
  $service_type = mysqli_real_escape_string($con, $_POST['service_type']);

  // Prepare the insert query
  $insert_query = "INSERT INTO appointments (fname, contact, date, time, service_type) VALUES ('$fname', '$contact',
'$date', '$time', '$service_type')";

  // Execute the query
  if (mysqli_query($con, $insert_query)) {
    // Redirect to the same page after inserting
    header("Location: Home_page.php");
    exit();
  } else {
    echo "Error inserting record: " . mysqli_error($con);
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="Home_page.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">
  <title>Denteeth</title>

</head>

<body>
  <nav>
    <a href="#Homepage">
      <div class="logo">
        <img src="img/logo.png">
      </div>
    </a>

    <ul>
      <li><a href="#Services">Services</a></li>
      <li><a href="#FaQ's"> FaQ's</a></li>
      <li><a href="#Appointment">Appointment</a></li>
      <li><a href="../login.php">Log In</a></li>
    </ul>
  </nav>
  <section id="Homepage">
    <center>
      <div class="img-area">

        <div id="s-bx">
          <h1>EHM</h1>
          <h3>Dental Clinic</h3>
          <br>
          <h2>Life's fair with Dental Care</h2>
          <br>
          <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
            Ut enim ad minim veniam, quis nostrud exercitation ullamco
            laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor
            in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
            Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt
            mollit anim id est laborum.
          </p>
          <br>
          <!--lagyan mu shadow yung box mu para halatang clickable-->
          <a href="" id="s-txt4">SMILE NOW!</a>
        </div>
      </div>
    </center>
    <h1 id="hdr-txt1">WHY US?</h1>
    <div id="container">
      <div>
        <i class="fa-regular fa-file my-icon"></i>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit,
          sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
          Ut enim ad minim veniam, quis nostrud exercitation ullamco
          laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor
          in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
          Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt
          mollit anim id est laborum.
        </p>
      </div>
      <div>
        <i class="fa-solid fa-tooth my-icon"></i>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit,
          sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
          Ut enim ad minim veniam, quis nostrud exercitation ullamco
          laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor
          in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
          Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt
          mollit anim id est laborum.
        </p>
      </div>
      <div>
        <i class="fa-solid fa-check my-icon"></i>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit,
          sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
          Ut enim ad minim veniam, quis nostrud exercitation ullamco
          laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor
          in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
          Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt
          mollit anim id est laborum.
        </p>
      </div>
    </div>
    <div id="side-img">
      <img src="https://i.pinimg.com/564x/76/a5/5e/76a55e28f41b42a5ed3b0c3356f90e9e.jpg" alt="mirror">
      <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit,
        sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
        Ut enim ad minim veniam, quis nostrud exercitation ullamco
        laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor
        in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt
        mollit anim id est laborum.
      </p>
    </div>
  </section>
  <section id="Services">
    <h1>Services</h1>
    <div id="crvs-container">
      <div class="img-box">
        <a href="SERVICES/Orthodontic_Braces.php">
          <div class="img-wrapper">
            <p>
              <?php echo htmlspecialchars($bracesData ? $bracesData['service_name'] : 'Orthodontic Braces'); ?>
            </p>
            <img
              src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($bracesData ? basename($bracesData['service_image']) : 'default.jpg'); ?>"
              alt="<?php echo htmlspecialchars($bracesData ? $bracesData['service_name'] : 'Orthodontic Braces'); ?>"
              onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
          </div>
        </a>
      </div>
      <div class="img-box">
        <a href="SERVICES/Dental_Cleaning.php">
          <div class="img-wrapper">
            <p>
              <?php echo htmlspecialchars($cleaningData ? $cleaningData['service_name'] : 'Dental Cleaning'); ?>
            </p>
            <img
              src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($cleaningData ? basename($cleaningData['service_image']) : 'default.jpg'); ?>"
              alt="<?php echo htmlspecialchars($cleaningData ? $cleaningData['service_name'] : 'Dental Cleaning'); ?>"
              onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
          </div>
        </a>
      </div>
      <div class="img-box">
        <a href="SERVICES/Dental_Whitening.php">
          <div class="img-wrapper">
            <p>
              <?php echo htmlspecialchars($whiteningData ? $whiteningData['service_name'] : 'Dental Whitening'); ?>
            </p>
            <img
              src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($whiteningData ? basename($whiteningData['service_image']) : 'default.jpg'); ?>"
              alt="<?php echo htmlspecialchars($whiteningData ? $whiteningData['service_name'] : 'Dental Whitening'); ?>"
              onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
          </div>
        </a>
      </div>
      <div class="img-box">
        <a href="SERVICES/Dental_Implants.php">
          <div class="img-wrapper">
            <p>
              <?php echo htmlspecialchars($implantsData ? $implantsData['service_name'] : 'Dental Implants'); ?>
            </p>
            <img
              src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($implantsData ? basename($implantsData['service_image']) : 'default.jpg'); ?>"
              alt="<?php echo htmlspecialchars($implantsData ? $implantsData['service_name'] : 'Dental Implants'); ?>"
              onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
          </div>
        </a>
      </div>
      <div class="img-box">
        <a href="SERVICES/Restoration.php">
          <div class="img-wrapper">
            <p>
              <?php echo htmlspecialchars($restorationData ? $restorationData['service_name'] : 'Restoration'); ?>
            </p>
            <img
              src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($restorationData ? basename($restorationData['service_image']) : 'default.jpg'); ?>"
              alt="<?php echo htmlspecialchars($restorationData ? $restorationData['service_name'] : 'Restoration'); ?>"
              onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
          </div>
        </a>
      </div>
      <div class="img-box">
        <a href="SERVICES/Extraction.php">
          <div class="img-wrapper">
            <p>
              <?php echo htmlspecialchars($extractionData ? $extractionData['service_name'] : 'Extraction'); ?>
            </p>
            <img
              src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($extractionData ? basename($extractionData['service_image']) : 'default.jpg'); ?>"
              alt="<?php echo htmlspecialchars($extractionData ? $extractionData['service_name'] : 'Extraction'); ?>"
              onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
          </div>
        </a>
      </div>
      <div class="img-box">
        <a href="SERVICES/All_Porcelain_Veneers_&_Zirconia.php">
          <div class="img-wrapper">
            <p>
              <?php echo htmlspecialchars($veneersData ? $veneersData['service_name'] : 'All Porcelain Veneers & Zirconia'); ?>
            </p>
            <img
              src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($veneersData ? basename($veneersData['service_image']) : 'default.jpg'); ?>"
              alt="<?php echo htmlspecialchars($veneersData ? $veneersData['service_name'] : 'All Porcelain Veneers & Zirconia'); ?>"
              onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
          </div>
        </a>
      </div>
      <div class="img-box">
        <a href="SERVICES/Full_Exam_&_X-Ray.php">
          <div class="img-wrapper">
            <p>
              <?php echo htmlspecialchars($examData ? $examData['service_name'] : 'Full Exam & X-Ray'); ?>
            </p>
            <img
              src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($examData ? basename($examData['service_image']) : 'default.jpg'); ?>"
              alt="<?php echo htmlspecialchars($examData ? $examData['service_name'] : 'Full Exam & X-Ray'); ?>"
              onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
          </div>
        </a>
      </div>
      <div class="img-box">
        <a href="SERVICES/Root_Canal_Treatment.php">
          <div class="img-wrapper">
            <p><?php echo htmlspecialchars($rootData ? $rootData['service_name'] : 'Root Canal Treatment'); ?>
            </p>
            <img
              src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($rootData ? basename($rootData['service_image']) : 'default.jpg'); ?>"
              alt="<?php echo htmlspecialchars($rootData ? $rootData['service_name'] : 'Root Canal Treatment'); ?>"
              onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
          </div>
        </a>
      </div>
      <div class="img-box">
        <a href="SERVICES/Dentures.php">
          <div class="img-wrapper">
            <p><?php echo htmlspecialchars($dentureData ? $dentureData['service_name'] : 'Dentures'); ?>
            </p>
            <img
              src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($dentureData ? basename($dentureData['service_image']) : 'default.jpg'); ?>"
              alt="<?php echo htmlspecialchars($dentureData ? $dentureData['service_name'] : 'Dentures'); ?>"
              onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
          </div>
        </a>
      </div>
      <div class="img-box">
        <a href="SERVICES/Crown_&_Bridge.php">
          <div class="img-wrapper">
            <p><?php echo htmlspecialchars($crownBridgeData ? $crownBridgeData['service_name'] : 'Crown & Bridge'); ?>
            </p>
            <img
              src="/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/<?php echo htmlspecialchars($crownBridgeData ? basename($crownBridgeData['service_image']) : 'default.jpg'); ?>"
              alt="<?php echo htmlspecialchars($crownBridgeData ? $crownBridgeData['service_name'] : 'Crown & Bridge'); ?>"
              onerror="this.src='/DENTAL/HOME_PAGE/SERVICES/SERVICES_IMAGES/default.jpg';">
          </div>
        </a>
      </div>

    </div>
  </section>
  <section id="Appointment">
    <h1>BOOK AN ANPPOINTMENT NOW</h1>
    <div class="loc">
      <a
        href="https://www.google.com/maps/place/QCU+-+San+Bartolome+Campus/@14.6999026,121.034423,3a,90y,307.65h,88.67t/data=!3m7!1e1!3m5!1s0wGbRKA75Zhha9AQHKnn3g!2e0!6shttps:%2F%2Fstreetviewpixels-pa.googleapis.com%2Fv1%2Fthumbnail%3Fpanoid%3D0wGbRKA75Zhha9AQHKnn3g%26cb_client%3Dsearch.gws-prod.gps%26w%3D211%26h%3D120%26yaw%3D304.3857%26pitch%3D0%26thumbfov%3D100!7i16384!8i8192!4m14!1m7!3m6!1s0x3397b16d7441f9a9:0x7e6f18165aacf9a1!2sQCU+-+San+Bartolome+Campus!8m2!3d14.6999881!4d121.0342928!16s%2Fg%2F11vjl8qr66!3m5!1s0x3397b16d7441f9a9:0x7e6f18165aacf9a1!8m2!3d14.6999881!4d121.0342928!16s%2Fg%2F11vjl8qr66?entry=ttu&g_ep=EgoyMDI0MDkxMC4wIKXMDSoASAFQAw%3D%3D">
        <img src="https://i.pinimg.com/564x/0b/8a/78/0b8a788dfe83416efe517e3ef089dea9.jpg" alt="map">
      </a>
    </div>
    <div class="form">
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

    <div class="icon">
      <a href="#Homepage">
        <i class="fa-solid fa-angle-up" style="color: #ffffff;"></i>
      </a>
    </div>
  </section>
</body>

</html>
</body>

</html>