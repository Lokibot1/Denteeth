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
                              WHERE (
                                (modified_date IS NOT NULL AND 
                                DATE(modified_date) = CURDATE()) 
                                OR (modified_date IS NULL AND 
                                DATE(date) = CURDATE())
                                ) AND status = '3'";


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
    <p>APPOINTMENT FOR THIS WEEK:</p>
    <?php
    // Get the start and end date of the current week
    $start_of_week = date('Y-m-d', strtotime('monday this week'));
    $end_of_week = date('Y-m-d', strtotime('sunday this week'));

    // Query to count appointments for the current week
    $sql_week = "SELECT COUNT(*) as total_appointments_week 
                 FROM tbl_appointments 
                 WHERE (
                    (modified_date IS NOT NULL AND 
                     WEEK(DATE(modified_date), 1) = WEEK(CURDATE(), 1) AND DATE(modified_date) != CURDATE())
                    OR 
                    (date IS NOT NULL AND 
                     WEEK(DATE(date), 1) = WEEK(CURDATE(), 1) AND DATE(date) > CURDATE())
                        )
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
    <p>APPOINTMENT FOR NEXT WEEK:</p>
    <?php
    // Get the start and end date of the current week
    $start_of_week = date('Y-m-d', strtotime('monday this week'));
    $end_of_week = date('Y-m-d', strtotime('sunday this week'));

    // Query to count appointments for the current week
    $sql_week = "SELECT COUNT(*) as total_appointments_week 
                 FROM tbl_appointments 
                 WHERE (
                    (modified_date IS NOT NULL AND 
                    WEEK(DATE(modified_date), 1) = WEEK(CURDATE(), 1) + 1 AND DATE(modified_date) != CURDATE())
                    OR 
                    (date IS NOT NULL AND 
                    WEEK(DATE(date), 1) = WEEK(CURDATE(), 1) + 1 AND DATE(date) > CURDATE())
                    )
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