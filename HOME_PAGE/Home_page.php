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

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Montserrat and Meddon -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..900&family=Meddon&display=swap" rel="stylesheet">


  <title>Denteeth</title>

</head>
<body>
  <nav>
    <a href="#Homepage">
      <div class="logo">
        <h1>EHM Dental Clinic</h1>
      </div>
    </a>
    <div class="hamburger">&#9776;</div>
    <ul>
        <li><a href="#Homepage">Home</a></li>
        <li><a href="#Services">Services</a></li>
        <li><a href="#About_Us"> About Us</a></li>
        <li><a href="#Contact_Us"> Contact Us</a></li>
        <li><a href="#Appointment">Book Appointment</a></li>
        <li><a href="../login.php">Log In</a></li>
    </ul>
</nav>

<div class="popup-overlay" id="termsPopup">
    <div class="popup">
        <div class="popup-header">
            <h2>Terms and Conditions</h2>
            <button class="close-btn" id="closePopup">&times;</button>
        </div>
        <div class="popup-content">
            <p>
            Welcome to <strong>EHM Dental Clinic</strong>. By using our website, you agree to comply with and be bound by the following terms and conditions. Please read them carefully before using our services.
            <br> <br>
                <strong>1. Acceptance of Terms</strong><br>
                By accessing and using our website, you confirm that you accept these Terms and Conditions in full. If you disagree with these terms, please do not use our website or book any appointments through it.
                <br><br>
                <strong>2. Use of Website</strong><br>
                The information provided on this website is for general purposes only and does not constitute medical advice. To book appointments, users must be 18 years or older, and minors must have a parent or guardian's consent. Unauthorized use of this website may result in claims for damages and/or criminal offenses.
                <br><br>
                <strong>3. Appointment Booking and Cancellation Policy</strong><br>
                Appointments are subject to availability and confirmation by the clinic. Users are responsible for providing accurate information when booking.
                Rescheduling or Cancellation of your appointment by contacting our clinic directly via phone. Appointments must be canceled or rescheduled at least 24 hours in advance.
                <br><br>
                <strong>4. Patient Responsibility</strong><br>
                Patients must provide accurate medical information and follow post-treatment care instructions.
                It is the patient’s responsibility to follow all post-treatment care instructions provided by the dentist.
                <br><br>
                <strong>5. Privacy and Data Protection</strong><br>
                We Collect Personal Information Collection.
                We do not share your personal information with third parties except where required by law or necessary to provide the service.
                In order to schedule an appointment, we collect the following personal information:
                <br>Full Name: <br>Contact Number: <br>Email Address: 
                <br><br>
                <strong>6. Payment Terms</strong><br>
                Payments must follow clinic policies. Some treatments may require advance payment or deposits.
                <br><br>
                <strong>7. Limitation of Liability</strong><br>
                While we strive to provide accurate information on our website, we do not guarantee the availability of appointment slots or the accuracy of any scheduling information provided online.
                The clinic reserves the right to cancel or reschedule appointments with prior notice under unavoidable circumstances (e.g., staff unavailability or emergencies) <br><br>
                <strong>8. Data Security</strong><br>
                We are committed to ensuring that your personal information is secure. We implement appropriate technical and organizational measures to protect your data from unauthorized access, disclosure, or loss. 
                However, we cannot guarantee the security of information transmitted over the internet and you acknowledge that any transmission of personal information is at your own risk.
                <br><br>
                <strong>9. Changes to Terms and Condition</strong><br>
                The clinic reserves the right to update or modify these terms at any time. Changes will be effective immediately upon posting on the website.
                It is the user’s responsibility to review these terms regularly to stay informed of any updates. <br><br>
                <strong>10. Intellectual Property Rights</strong><br>
                All content on this website, including text, images, logos, and trademarks, is the property of the 
               <strong>Group 4 of QCU SBIT - 2I 2024</strong>and protected by copyright laws.
                You may not reproduce or redistribute any content without prior written permission from the clinic, and to the stated owners above. <br> <br>
                <strong>11. Governing Law</strong><br>
                These terms and conditions are governed by and constructed in accordance with the laws of the Philippines, and you irrevocably submit to the exclusive jurisdiction of the courts in our country.
                <br><br><strong>12. Contact Information of the Clinic</strong><br>
                Phone: 09088975285 | Telephone: 87030319 | Address: 191 Kaingin Rd, Quezon City, 1100 Metro Manila
            </p>
        </div>
        <div class="popup-buttons">
            <button id="declineBtn" class="btn decline-btn">Decline</button>
            <button id="acceptBtn" class="btn accept-btn">Accept</button>
        </div>
    </div>
</div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const popup = document.getElementById('termsPopup');
      popup.style.display = 'flex'; // Show the popup

      document.getElementById('acceptBtn').addEventListener('click', function () {
        popup.style.display = 'none'; // Hide the popup on accept
      });
      document.getElementById('closePopup').addEventListener('click', function () {
        popup.style.display = 'none'; 
      });
    });
  </script>


<div class="sidebar" id="sidebar">
    <div class="close-btn" id="close-btn">&times;</div>
    <ul>
        <li><a href="#Homepage">Home</a></li>
        <li><a href="#Services">Services</a></li>
        <li><a href="#About_Us"> About Us</a></li>
        <li><a href="#Contact_Us"> Contact Us</a></li>
        <li><a href="#Appointment">Book Appointment</a></li>
        <li><a href="../login.php">Log In</a></li>
    </ul>
</div>
  <section id="Homepage">
    <center>
      <div class="img-area">

        <div id="s-bx">
          <div class="s-img">
            <img src="img/logo.png" alt="">
          </div>
          <h1>EHM</h1>
          <h2>Dental Clinic
            <br>
            ┈┈┈┈┈┈
            <br>
            Laboratory
          </h2>
          <br>
          <h3>Life's fair with Dental Care</h3>
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
          <a href="#Appointment  " id="s-txt4">SMILE NOW!</a>
        </div>
      </div>
    </center>
    <h1 id="hdr-txt1">WHY EHM?</h1>
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
        <br>
        <br>
        <p>
        At EHM Dental Clinic, we believe that “Life is fair with dental care”. 
        With our expert doctor and compassionate dental assistant, we make your dental health 
        our top priority. We are committed to provide you with personalized attention to  patients 
        ensuring that you leave with the confidence of a brighter smile.
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
      <div>
        <img src="img/s-img.png" alt="mirror">
      </div>
      <br>
      <br>
      <div class="s-txt">
      <span>We value the quality of our Dental Works</span>
        <p>
        At EHM Dental Clinic, we are dedicated to providing you top-quality dental care. We ensure that 
      every treatment is done with precision, using the latest technology to give you the best results. 
      Ensuring that you leave with a brighter and healthier smile.
      </p>
      </div>
    </div>
  </section>
  <section id="About_Us">
    <h1 class="About_Us">ABOUT US</h1>
    <div id="abt-right">
      <div>
        <img src="img/abt-img1.png" alt="mirror">
      </div>
      <br>
      <br>
      <div class="abtr-txt">
      <span>We value the quality of our Dental Works</span>
        <p>
        At EHM Dental Clinic, we are dedicated to providing you top-quality dental care. We ensure that 
      every treatment is done with precision, using the latest technology to give you the best results. 
      Ensuring that you leave with a brighter and healthier smile.
      </p>
      </div>
    </div>

<div id="abt-left">
  <div>
    <img src="img/abt-img1.png" alt="mirror">
  </div>
    <br>
    <br>
  <div class="abtl-txt">
    <span>We value the quality of our Dental Works</span>
      <p>
      At EHM Dental Clinic, we are dedicated to providing you top-quality dental care. We ensure that 
      every treatment is done with precision, using the latest technology to give you the best results. 
      Ensuring that you leave with a brighter and healthier smile.
      </p>
  </div>
</div>

<div id="abt-right">
      <div>
        <img src="img/abt-img3.png" alt="mirror">
      </div>
      <br>
      <br>
      <div class="abtr-txt2">
      <span>We value the quality of our Dental Works</span>
        <p>
        At EHM Dental Clinic, we are dedicated to providing you top-quality dental care. We ensure that 
      every treatment is done with precision, using the latest technology to give you the best results. 
      Ensuring that you leave with a brighter and healthier smile.
      </p>
      </div>
    </div>
  </section>
  <section id="Services">
    <h1>SERVICES</h1>
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
    <div class="Appointment">
        <center>
            <div class="apt-container">
                <div class="loc">
                    <div class="loc-img">
                        <a href="">
                            <img src="img/loc.png" alt="Facebook Logo">
                        </a>
                        <h3>Our Location</h3>
                    </div>
                </div>
                <div class="form-cont">
                    <h3>BOOK AN APPOINTMENT HERE</h3>
                    <div class="form">
              <form method="POST" action="">
              <label for="fname">Name:</label>
              <input type="text" name="fname" id="modal-fname" placeholder="Enter your name" required><br>
              <label for="contact">Contact:</label>
        <input type="text" name="contact" id="modal-contact" placeholder="Enter your contact number" required><br>
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
            </div>
        </center>
    </div>
</section>

  <section id="Contact_Us">
    <div class="ContactUs">
        <center>
            <h1>CONTACT US</h1> 
            <p>_____________________________________________________________________________________________________________-</p>
            <div class="fb_container">
                <div class="fb">
                    <h3>YOU CAN FOLLOW US ON</h3>
                    <span class="fb-span">FACEBOOK</span>
                    <div class="fb-img-area">
                        <a href="">
                            <img src="img/fb.png" alt="Facebook Logo">
                        </a>
                        <h3>EHM Dental Clinic</h3>
                    </div>
                </div>
                <div class="fb2">
                    <h3>YOU CAN ALSO CONTACT US ON:</h3>
                    <div class="icon">
                        <i class="fa-solid fa-mobile"></i>
                        <p>Mobile No.: 09088975285</p>
                    </div>
                    <div class="icon">
                        <i class="fa-solid fa-phone"></i>
                        <p>Telephone No.: 87030319</p>
                    </div>
                </div>
            </div>
        </center>
    </div>
</section>

<div class="btnup">
    <a href="#Homepage">
        <i class="fa-solid fa-angle-up"></i>
    </a>
</div>

</body>

</html>
</body>

</html>