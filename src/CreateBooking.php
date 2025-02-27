<?php
// WRITTEN BY JEFFREY GAN

session_start();

if (!isset($_SESSION['email'])) {
    // return to landing page if not logged in
    header('Location: landing_page.html?login=false');
    exit;
}
$email = $_SESSION['email'];
$is_member = $_SESSION['is_member'];

if ($is_member == FALSE) {
    // return bad request if not member
    echo "400: Bad Request";
    exit;
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    //$data = htmlspecialchars($data);
    return $data;
}

function generateURL($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

// hold values for prepared bookingSlot mysql statement
$bs_dates = array();
$bs_start_times = array();
$bs_add_times = array();

function addBookingSlotValues($date_yymmdd, $start_timesec, $add_timesec) {
    global $bs_dates;
    global $bs_start_times;
    global $bs_add_times;
    // push to end of array
    $bs_dates[] = $date_yymmdd;
    $bs_start_times[] = $start_timesec;
    $bs_add_times[] = $add_timesec;
}

function makeBookingSlotValues_OneDay($date, $startTime, $numOfMeetings, $lenMeeting) {
    $date_yymmdd = date_format($date, "Y-m-d");
    for ($i = 0; $i < $numOfMeetings; $i++) {
        addBookingSlotValues($date_yymmdd, $startTime, $i * $lenMeeting);
    }
}

// connect to db
$servername = "mysql.cs.mcgill.ca";
$dbname = "fall2024-comp307-jgan10";
$username = "comp307-jgan10";
$password = "0eAjoerfZhJHYer";

// Create connection
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo '500: Internal Server Error - Database Connection Failed';
    exit;
}

// Generate a unique URL
$url = generateURL();
while (true) {
    $sql = "SELECT * FROM `booking` WHERE `url` = '$url'";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        break;
    }
}

// Get information from the HTML form
$event_type = test_input($_POST['event-type']);
$title = test_input($_POST['title']);
$desc = test_input($_POST['desc']);
if (strlen($desc) == 0) {
    $desc = "No description provided";
}

$start_times = $_POST['start-times'];
$num_meetings= $_POST['num-meetings'];
$len_meetings= $_POST['len-meetings'];

$timezone = new DateTimeZone("America/New_York");
$start_date = new DateTimeImmutable(test_input($_POST['start-date']), $timezone);

# Generate BookingSlot queries
if ($event_type === 'SINGLE') {
    $end_date = $start_date;
    $s_time = strtotime($start_times[0]) - strtotime("00:00:00"); // time HH:mm:ss to seconds
    $l_time = $len_meetings[0] * 60; // length (minutes) to seconds
    makeBookingSlotValues_OneDay($start_date, $s_time, $num_meetings[0], $l_time);
    
} else if ($event_type === 'WEEKLY') {
    $end_date = new DateTimeImmutable(test_input($_POST['end-date']), $timezone);
    $day_weeks = $_POST['day-of-week'];

    for ($i = 0; $i < count($day_weeks); $i++) {
        $s_time = strtotime($start_times[$i]) - strtotime("00:00:00");
        $l_time = $len_meetings[$i] * 60;
        $n_meet = $num_meetings[$i];
        $day_week = $day_weeks[$i];

        $date = $start_date->modify($day_week);
        while ($date <= $end_date) {
            makeBookingSlotValues_OneDay($date, $s_time, $n_meet, $l_time);
            $date = $date->modify("next " . $day_week);
        }
    }

} else if ($event_type === 'MONTHLY') {
    $end_date = new DateTimeImmutable(test_input($_POST['end-date']), $timezone);
    $month_options = $_POST['month-option'];

    $dow_count = 0;
    $dom_count = 0;

    for ($i = 0; $i < count($month_options); $i++) {
        $s_time = strtotime($start_times[$i]) - strtotime("00:00:00");
        $l_time = $len_meetings[$i] * 60;
        $n_meet = $num_meetings[$i];

        $option = $month_options[$i];
        if ($option === "DOW") {
            $nth_dow = $_POST['num-dow'][$dow_count];
            $day_week = $_POST['day-of-week'][$dow_count];
            $dow_count++;

            $date = $start_date;
            while ($date <= $end_date) {
                $d = $date->modify($nth_dow . " " . $day_week ." of this month");
                if ($d >= $start_date and $d <=$end_date ) {
                    makeBookingSlotValues_OneDay($d, $s_time, $n_meet, $l_time);
                }
                $date = $date->modify("first day of next month");
            }

        } else if ($option === "DOM") {
            $nth_dom = $_POST['num-of-month'][$dom_count];
            $dom_count++;

            $date = $start_date->modify("first day of this month");
            while ($date <= $end_date) {
                
                if ($nth_dom > 28) { // clip to last day
                    $last = $date->modify("last day of this month");
                    if ($nth_dom > $last->format("d")) {
                        makeBookingSlotValues_OneDay($last, $s_time, $n_meet, $l_time);
                        $date = $date->modify("first day of next month");
                        continue;
                    }
                }
                $d = $date->modify($nth_dom-1 . " days");
                if ($d >= $start_date and $d <=$end_date ) {
                    makeBookingSlotValues_OneDay($d, $s_time, $n_meet, $l_time);
                }
                
                $date = $date->modify("first day of next month");
            }

        }

    }
    
} else {
    $conn->close();
    die('Invalid event_type');
}

// Create the booking record
$start_date_str = date_format($start_date, "Y-m-d");
$end_date_str = date_format($end_date, "Y-m-d");

$type = "REPEAT";
if ($event_type === "SINGLE") {
    $type = "SINGLE";
}

// Prepare Booking statement
$bookingStmt = $conn->prepare("INSERT INTO booking (url, mEmail, title, description, type, startdate, enddate)
VALUES (?, ?, ?, ?, ?, ?, ?)");

// Insert booking
$bookingStmt->bind_param("sssssss", $url, $email, $title, $desc, $type, $start_date_str, $end_date_str);
$bookingStmt->execute();

// Prepare bookingSlot statement
$bookingSlotStmt = $conn->prepare("INSERT INTO bookingslot (url, slotId, date, time, uEmail) 
VALUES (?, ?, DATE(?), ADDTIME(SEC_TO_TIME(?),SEC_TO_TIME(?)) , NULL)");

// Insert BookingSlots
for ($i = 0; $i < count($bs_dates); $i++) {
    // url, slotId, date, startTime, offsetTime
    $slotId = $i + 1;
    $bookingSlotStmt->bind_param("sisii", $url, $slotId, $bs_dates[$i], $bs_start_times[$i], $bs_add_times[$i]);
    $bookingSlotStmt->execute();
}
$conn->close();

header('Location: create_meeting_success.php?url='.$url);
exit;
?>