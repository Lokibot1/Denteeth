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

if (isset($_POST['update'])) {
  $id = isset($_POST['id']) ? $_POST['id'] : '';
  $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
  $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
  $middle_name = mysqli_real_escape_string($con, $_POST['middle_name']);
  $contact = mysqli_real_escape_string($con, $_POST['contact']);
  $date = date('Y-m-d'); // Set date to today's date
  $time = mysqli_real_escape_string($con, $_POST['time']);
  $service_type = mysqli_real_escape_string($con, $_POST['service_type']);

  // Convert the selected time to 24-hour format
  $time_24hr = DateTime::createFromFormat('h:i A', $time)->format('H:i:s');

  // Check for exact time conflicts only for today's date
  $check_time_query = "
        SELECT id, time 
        FROM tbl_appointments 
        WHERE date = '$date' 
        AND TIME(time) = TIME('$time_24hr')
    ";

  $time_result = mysqli_query($con, $check_time_query);
  $time_row = mysqli_fetch_assoc($time_result);

  if ($time_row) {
    // Conflict found
    echo "<script>alert('The selected time conflicts with another appointment for today. Please choose a different time.');</script>";
  } else {
    // No conflict - proceed with inserting appointment
    $insert_patient_query = "
            INSERT INTO tbl_patient (first_name, last_name, middle_name) 
            VALUES ('$first_name', '$last_name', '$middle_name')
        ";

    if (mysqli_query($con, $insert_patient_query)) {
      $patient_id = mysqli_insert_id($con);
      $insert_appointment_query = "
                INSERT INTO tbl_appointments (id, name, contact, date, time, service_type) 
                VALUES ('$patient_id', '$patient_id', '$contact', '$date', '$time_24hr', '$service_type')
            ";

      if (mysqli_query($con, $insert_appointment_query)) {
        header("Location: Home_page.php");
        exit();
      } else {
        echo "Error updating appointment record: " . mysqli_error($con);
      }
    } else {
      echo "Error updating patient record: " . mysqli_error($con);
    }
  }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="home.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Montserrat and Meddon -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..900&family=Meddon&display=swap"
    rel="stylesheet">


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
      <li><a href="#About_Us"> About Us</a></li>
      <li><a href="#Services">Services</a></li>
      <li><a href="#Appointment">Book Appointment</a></li>
      <li><a href="#Contact_Us"> Contact Us</a></li>
    </ul>
  </nav>
  <div class="sidebar" id="sidebar">
    <div class="close-btn" id="close-btn">&times;</div>
    <ul>
      <li><a href="#Homepage">Home</a></li>
      <li><a href="#About_Us"> About Us</a></li>
      <li><a href="#Services">Services</a></li>
      <li><a href="#Appointment">Book Appointment</a></li>
      <li><a href="#Contact_Us"> Contact Us</a></li>
    </ul>
  </div>
  <script>
    // JavaScript for Sidebar Toggle
    const hamburger = document.querySelector('.hamburger');
    const sidebar = document.getElementById('sidebar');
    const closeBtn = document.getElementById('close-btn');

    hamburger.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });

    closeBtn.addEventListener('click', () => {
      sidebar.classList.remove('active');
    });
  </script>
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
          <h3>Life's fair, with Dental Care</h3>
          <br>
          <p>
            Healthy Smile starts within us. At EHM Dental Clinic, we are dedicated
            to making your smile vibrant and your dental health exceptional. Book an
            Appointment with us today, and let’s help your smile shine its brightest!
          </p>
          <br>
          <a href="#Appointment  " id="s-txt4">SMILE NOW!</a>
        </div>
      </div>
    </center>
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
        <span>Your Trusted Partner in Dental Care Since 2011</span>
        <p>
          Established on January 18, 2011, EHM Dental Clinic in Apolonio Samson,
          Quezon City, is dedicated to providing exceptional dental care. With skilled
          professionals and modern techniques, the clinic ensures a friendly and comfortable
          environment for all patients.
        </p>
      </div>
    </div>
    <h1 id="hdr-txt1">WHY EHM?</h1>
    <div id="container">
      <div>
        <i class="fa-regular fa-file my-icon"></i>
        <br>
        <p>
          Our clinic is staffed with highly trained and experienced dental professionals
          committed to providing exceptional care. We adhere to industry standards and operate
          with complete legal documentation to ensure the highest level of trust and quality
          for our patients.
        </p>
      </div>
      <div>
        <i class="fa-solid fa-tooth my-icon"></i>
        <br>
        <p>
          <b>Expert Care:</b> Our experienced professionals are committed to providing the
          highest quality dental services tailored to your unique needs. <br>
          <b>Easy to Understand: </b>We're here to answer your questions, explain your
          treatment options, and ensure you have a clear understanding of your dental care.
        </p>
      </div>
      <div>
        <i class="fa-solid fa-check my-icon"></i>
        <br>
        <p>
          <b>Friendly, Caring Team:</b> Our warm and welcoming staff is here to greet you
          with a smile and ensure you feel right at home from the moment you step through
          our doors. <br>
          <b>Calm and Relaxed Atmosphere:</b> We've created a soothing environment, so
          you can put your nerves at ease. Your dental health should be a stress-free experience.
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
  <script>
    document.querySelectorAll('.img-box a').forEach(link => {
      link.addEventListener('click', function (event) {
        event.preventDefault(); // Prevent immediate navigation
        const targetUrl = this.href;

        // Add fade-out animation
        document.getElementById('crvs-container').classList.add('fade-out');

        // Delay navigation until animation ends
        setTimeout(() => {
          window.location.href = targetUrl;
        }, 500); // Adjust this to match CSS animation duration
      });
    });
  </script>
  <section id="Appointment">
    <div class="Appointment">
      <center>
        <div class="apt-container">
          <div class="loc">
            <div class="loc-img">
              <a href="">
                <iframe
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3859.977401090534!2d121.00833437393719!3d14.657223975690215!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b66038db6f6b%3A0x77228c7173b33747!2sEHM%20Dental%20Clinic!5e0!3m2!1sen!2sph!4v1729854317715!5m2!1sen!2sph"
                  width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"></iframe>
              </a>
              <h3>OUR LOCATION</h3>
            </div>
          </div>
          <div class="form-cont">
            <h3>BOOK AN APPOINTMENT HERE</h3>
            <div class="form">
              <form method="POST" action="" id="appointmentForm">
                <label for="modal-name">Full Name: <br> (Last Name, First Name, Middle Initial)</label>
                <div class="name-fields">
                  <input type="text" name="last_name" id="modal-last-name" placeholder="Enter Last Name" required>
                  <input type="text" name="first_name" id="modal-first-name" placeholder="Enter First Name" required>
                  <input type="text" name="middle_name" id="modal-middle-name" placeholder="Enter Middle Initial">
                </div>
                <label for="contact">Contact:</label>
                <input type="text" name="contact" id="modal-contact" placeholder="Enter your contact number"
                  maxlength="11" required pattern="\d{11}" title="Please enter exactly 11 digits"><br>
                <label for="date">Date:</label>
                <input type="date" name="date" id="modal-date" required><br>
                <label for="time">Time: <br> (Will only accept appointments from 9:00 a.m to 6:00 p.m)</label>
                <select name="time" id="modal-time" required>
                  <option value="09:00 AM">09:00 AM</option>
                  <option value="10:30 AM">10:30 AM</option>
                  <option value="12:00 PM" disabled>12:00 AM (Lunch Break)</option>
                  <option value="12:30 PM">12:30 PM</option>
                  <option value="01:30 PM">01:30 PM</option>
                  <option value="03:00 PM">03:00 PM</option>
                  <option value="04:30 PM">04:30 PM</option>
                </select>
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
                <button type="button" id="bookBtn" class="bookBtn">BOOK</button>
                <!-- Terms and Conditions Popup -->
                <div class="popup-overlay" id="termsPopup" style="display: none;">
                  <div class="popup">
                    <div class="popup-header">
                      <h2>Terms and Conditions</h2>
                      <button class="close-btn" id="closePopup">&times;</button>
                    </div>
                    <div class="popup-content">
                      <p><strong>1. Acceptance of Terms</strong><br>
                        By accessing and using our website, you confirm that you accept these Terms and Conditions in
                        full. If you
                        disagree with these terms, please do not use our website or book any appointments through it.
                      </p>
                      <p>
                        <strong>2. Use of Website</strong><br>
                        The information provided on this website is for general purposes only and does not constitute
                        medical advice.
                        Users must be 18 years or older to book appointments, and minors must have a parent or
                        guardian's consent.
                        Unauthorized use of this website may result in claims for damages and/or criminal offenses.
                      </p>
                      <p>
                        <strong>3. Appointment Booking and Cancellation Policy</strong><br>
                        Appointments are subject to availability and confirmation by the clinic. Users are responsible
                        for providing
                        accurate information when booking. Rescheduling or cancellation of your appointment must be done
                        by contacting
                        our clinic directly via phone. Appointments must be canceled or rescheduled at least 24 hours in
                        advance.
                      </p>
                      <p>
                        <strong>4. Patient Responsibility</strong><br>
                        Patients must provide accurate medical information and follow all post-treatment care
                        instructions.
                        It is the patient’s responsibility to adhere to all instructions provided by the dentist.
                      </p>
                      <p>
                        <strong>5. Privacy and Data Protection</strong><br>
                        We collect personal information necessary to schedule appointments. We do not share your
                        personal information with third parties except where required by law or necessary to provide the
                        service.
                        In order to schedule an appointment, we collect the following personal information:
                        <br>Full Name:
                        <br>Contact Number:
                        <br>Email Address:
                      </p>
                      <p>
                        <strong>6. Payment Terms</strong><br>
                        Payments must follow clinic policies. Some treatments may require advance payment or deposits.
                      </p>
                      <p>
                        <strong>7. Limitation of Liability</strong><br>
                        While we strive to provide accurate information on our website, we do not guarantee the
                        availability of
                        appointment slots or the accuracy of any scheduling information provided online. The clinic
                        reserves the right to cancel or reschedule appointments with prior notice under unavoidable
                        circumstances (e.g., staff unavailability or emergencies).
                      </p>
                      <p>
                        <strong>8. Data Security</strong><br>
                        We are committed to ensuring that your personal information is secure. We implement appropriate
                        technical and
                        organizational measures to protect your data from unauthorized access, disclosure, or loss.
                        However, we cannot guarantee the security of information transmitted over the internet; you
                        acknowledge that any transmission of personal information is at your own risk.
                      </p>
                      <p>
                        <strong>9. Changes to Terms and Conditions</strong><br>
                        The clinic reserves the right to update or modify these terms at any time. Changes will be
                        effective
                        immediately upon posting on the website. It is the user’s responsibility to review these terms
                        regularly to stay informed of any updates.
                      </p>
                      <p>
                        <strong>10. Intellectual Property Rights</strong><br>
                        All content on this website, including text, images, logos, and trademarks, is the property of
                        <strong>Group 4 of QCU SBIT - 2I 2024</strong> and is protected by copyright laws. You may not
                        reproduce or redistribute any content without prior written permission from the clinic and the
                        stated owners.
                      </p>
                      <p>
                        <strong>11. Governing Law</strong><br>
                        These terms and conditions are governed by and construed in accordance with the laws of the
                        Philippines, and
                        you irrevocably submit to the exclusive jurisdiction of the courts in our country.
                      </p>
                      <p>
                        <strong>12. Contact Information of the Clinic</strong><br>
                        Phone: 09088975285 | Telephone: 87030319 | Address: 191 Kaingin Rd, Quezon City, 1100 Metro
                        Manila
                      </p>
                    </div>
                    <div class="popup-buttons">
                      <input type="submit" name="update" value="Accept" class="bookBtn " id="acceptBtn">
                    </div>
                  </div>
                </div>
              </form>

              <div id="notification" class="notification" style="display: none;">
                <p>Your appointment has been successfully booked!</p>
                <button onclick="closeNotification()">OK</button>
              </div>
            </div>
          </div>

          <script>
            document.getElementById('bookBtn').addEventListener('click', function (event) {
              if (validateForm()) {
                document.getElementById('termsPopup').style.display = 'block';
              } else {
                alert("Please fill out all fields before booking.");
              }
            });

            document.getElementById('closePopup').addEventListener('click', function () {
              document.getElementById('termsPopup').style.display = 'none';
            });

            document.getElementById('acceptBtn').addEventListener('click', function () {
              document.getElementById('termsPopup').style.display = 'none';
              showNotification();
            });

            function showNotification() {
              document.getElementById("notification").style.display = "block";
            }

            function closeNotification() {
              document.getElementById("notification").style.display = "none";
            }

            function validateForm() {
              const form = document.getElementById('appointmentForm');
              const inputs = form.querySelectorAll('input[required], select[required]');

              for (const input of inputs) {
                if (!input.value) {
                  return false;
                }
              }
              return true;
            }
            // Set min and max date for current week
            window.onload = function () {
              // Get today's date
              const today = new Date();
              // Calculate the start (today) and end (six days from today) of the current week
              const firstDay = new Date(today); // Start of the week (today)
              const lastDay = new Date(firstDay);
              lastDay.setDate(firstDay.getDate() + 6); // End of the week (six days from today)
              // Set min and max for the date input
              document.getElementById('modal-date').addEventListener('change', function () {
                const selectedDate = this.value;
                // Make an AJAX request to check appointment count for the selected date
                fetch(`check_appointments.php?date=${selectedDate}`)
                  .then(response => response.json())
                  .then(data => {
                    const timeOptions = document.getElementById('modal-time').options;
                    // Reset all time options first
                    for (let i = 0; i < timeOptions.length; i++) {
                      timeOptions[i].disabled = false; // Enable all options initially
                    }

                    // Loop through existing appointments and disable selected time slots
                    data.takenTimes.forEach(takenTime => {
                      for (let i = 0; i < timeOptions.length; i++) {
                        if (timeOptions[i].value === takenTime) {
                          timeOptions[i].disabled = true; // Disable taken time slot
                        }
                      }
                    });
                  })
                  .catch(error => console.error("Error:", error));
              });

              // Check for conflicts between selected date and time
              document.getElementById('modal-time').addEventListener('change', function () {
                const date = document.getElementById('modal-date').value;
                const time = this.value;

                if (date && time) {
                  fetch(`check_appointments.php?date=${date}&time=${time}`)
                    .then(response => response.json())
                    .then(data => {
                      if (data.conflict) {
                        alert("The selected time conflicts with another appointment. Please choose a different time.");
                        this.value = ""; // Clear the time input
                      }
                    })
                    .catch(error => console.error("Error:", error));
                }
              });

              document.getElementById('modal-date').setAttribute('min', formatDate(firstDay));
              document.getElementById('modal-date').setAttribute('max', formatDate(lastDay));
              // Format date as YYYY-MM-DD
              function formatDate(date) {
                const year = date.getFullYear();
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const day = date.getDate().toString().padStart(2, '0');
                return `${year}-${month}-${day}`;
              }
              // Display the moving week in a console or a designated HTML element
              const weekDays = [];
              for (let i = 0; i < 7; i++) {
                const currentDay = new Date(firstDay);
                currentDay.setDate(firstDay.getDate() + i); // Get each day of the week
                weekDays.push(formatDate(currentDay)); // Format and add to array
              }
              // Set min and max time dynamically
              document.getElementById('modal-time').setAttribute('min', '09:00');
              document.getElementById('modal-time').setAttribute('max', '18:00');
              // Example output: Display the moving week in the console
              console.log(weekDays.join(' ')); // You can also display this in the UI instead
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
        <p>If you have any concerns, please reach out to us via:</p>
        <div class="fb_container">
          <div class="fb">
            <h3>YOU CAN FOLLOW US ON</h3>
            <span class="fb-span">FACEBOOK</span>
            <div class="fb-img-area">
              <a href="https://www.facebook.com/ehmdentalclinic">
                <img src="img/fb.png" alt="Facebook Logo">
              </a>
              <h3 style="color: #F9F2B4;">EHM Dental Clinic</h3>
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