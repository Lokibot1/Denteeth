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
$sql = "SELECT * FROM services WHERE service_name = ?";
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

$stmt->close();
$con->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">
  <title>Services</title>
  <style>

  </style>
</head>

<body>
  <nav>
    <a href="homepage.php">
      <div class="logo">
        <img src="../img/logo.png">
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
      <button class="button">BOOK APPOINTMENT NOW</button>
    </div>
  </div>
</body>

</html>