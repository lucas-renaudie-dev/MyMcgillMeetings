<?php
// Written by Ping-Chieh Tu and Lucas Renaudie
// Start the session
session_start();

// Retrieve and validate the 'url' parameter
if (!isset($_GET["url"])) {
    http_response_code(400);
    echo '400: Bad Request - Missing URL Parameter';
    exit;
}

$url = $_GET["url"];

// Validate that 'url' is alphanumeric and exactly 10 characters
if (!ctype_alnum($url) || strlen($url) !== 10) {
    http_response_code(400);
    echo '400: Bad Request - Invalid URL Parameter';
    exit;
}

// Get the current URL for redirection purposes
$current_url = urlencode($_SERVER['REQUEST_URI']);

// Database connection parameters
$servername = "mysql.cs.mcgill.ca";
$dbname = "fall2024-comp307-jgan10";
$username = "comp307-jgan10";
$password = "0eAjoerfZhJHYer";

// Create connection using mysqli
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo '500: Internal Server Error - Database Connection Failed';
    exit;
}

// Function to format dates
function formatDate($date_str) {
    $date = DateTime::createFromFormat('Y-m-d', $date_str);
    if ($date && $date->format('Y-m-d') === $date_str) {
        return $date->format('M d, Y'); // "Dec 20, 2024"
    } else {
        return "Invalid Date";
    }
}

// Prepare and execute the first SQL statement securely
$stmt1 = $conn->prepare("SELECT * FROM `booking` WHERE `url` = ?");
$stmt1->bind_param("s", $url);
$stmt1->execute();
$result = $stmt1->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $title = $row['title'];
        $mEmail = $row['mEmail'];
        $des = $row['description'];
        $type = $row['type'];
        $sdate = $row['startdate']; // e.g., "2024-12-20"
        $edate = $row['enddate'];   // e.g., "2024-12-25"

        // Include necessary files
        include('header.txt');
        if (sizeof($_GET) == 1) {
            include('urlcss.txt');
        } else if (isset($_GET["Page"]) && $_GET["Page"] == "Req") {
            include('reqcss.txt');
        }
        include('head.txt');

        // Include menu based on session
        if (!isset($_SESSION["email"])) {
            include('menu-not-logged-in.txt');
        } else {
            if ($_SESSION["is_member"]) {
                include('menu-logged-in-member.txt');
            } else {
                include('menu-logged-in-user.txt');
            }
        }

        // Start outputting meeting details
        echo '
        <div class="main-content">
            <div class="meeting-details">
                <div class="meeting-title">Meeting Details</div>
                <p><strong>Host:</strong>';

        // Fetch Host's First and Last Name using Prepared Statements
        $stmt2 = $conn->prepare("SELECT `fname`, `lname` FROM `member` WHERE `mEmail` = ?");
        $stmt2->bind_param("s", $mEmail);
        $stmt2->execute();
        $nested_result = $stmt2->get_result();

        if ($nested_result->num_rows > 0) {
            while ($host = $nested_result->fetch_assoc()) {
                $fname = $host["fname"];
                $lname = $host["lname"];
                echo ' ' . htmlspecialchars($fname) . ' ' . htmlspecialchars($lname) . '</p>';
            }
        } else {
            echo ' Unknown Host</p>';
        }

        echo '
              <div class="office-hours">
                <div class="span-title"> ' . htmlspecialchars($title) . ' <br></div>
                <div class="span-desc" ' . (empty($des)  ? 'style="margin-bottom: -15px;"' : '') . '><p>' . (empty($des) ? '' : 'Description: ' . htmlspecialchars($des)) . '</p></div>                   
                    <ul>
                        <li><span>Start Date:</span><span>' . htmlspecialchars(formatDate($sdate)) . '</span></li>
                        <li><span>End Date:</span><span>' . htmlspecialchars(formatDate($edate)) . '</span></li>
                    </ul>
              </div>';

        // Handling Booking Slots
        if (sizeof($_GET) == 1) {
            $stmt3 = $conn->prepare("SELECT * FROM `bookingslot` WHERE `url` = ? AND `uEmail` IS NULL AND `date` >= CURDATE() AND `date` BETWEEN ? AND ? ORDER BY `date` ASC");
            $stmt3->bind_param("sss", $url, $sdate, $edate);
            $stmt3->execute();
            $nested_result2 = $stmt3->get_result();

            echo '
                <!-- Next Available Block -->
                <div id="next-available" class="next-available">
                    <div class="next-av">Next available times:</div>
                    <form action="book_meeting.php" method="POST" id="bookingForm">
                        <select id="next-available-select" name="bookingTime">';

            // Initialize $hasSlots
            $hasSlots = false;

            if ($nested_result2->num_rows > 0) {
                $hasSlots = true; // Slots are available
                while ($slot = $nested_result2->fetch_assoc()) {
                    $date = $slot["date"];     // e.g., "2024-12-20"
                    $time = $slot["time"];     // e.g., "10:32" or "10:32:00"
                    $slotId = $slot["slotId"];

                    // Validate date format
                    $date_valid = DateTime::createFromFormat('Y-m-d', $date) !== false;

                    // Validate time format (either H:i or H:i:s)
                    $time_valid = DateTime::createFromFormat('H:i', $time) || DateTime::createFromFormat('H:i:s', $time);

                    if ($date_valid && $time_valid) {
                        // Normalize the time format by removing seconds if present
                        $time_parts = explode(':', $time);
                        if (count($time_parts) == 3) {
                            $time = $time_parts[0] . ':' . $time_parts[1]; // "10:32"
                        }

                        // Combine date and normalized time
                        $datetime_str = $date . ' ' . $time; // "2024-12-20 10:32"

                        // Create DateTime object
                        $datetime = DateTime::createFromFormat('Y-m-d H:i', $datetime_str);

                        if ($datetime) {
                            // Format DateTime object into desired format
                            $formatted_datetime = $datetime->format('M d, Y, h:i A'); // "Dec 20, 2024, 10:32 AM"
                        } else {
                            // Handle invalid date/time formats
                            $formatted_datetime = "Invalid Date/Time";
                        }
                    } else {
                        // Handle invalid date or time formats
                        $formatted_datetime = "Invalid Date/Time";
                    }

                    // Output the option element with proper escaping
                    echo '<option value="' . htmlspecialchars($slotId) . '">' . htmlspecialchars($formatted_datetime) . '</option>';
                }
            } else {
                echo '<option value="">No Available Slots</option>';
            }

            echo '</select>
                    <input type="hidden" name="url" value="' . htmlspecialchars($url) . '">
                </form>
            </div>';

            // Handling Login and Booking Buttons
            if (!isset($_SESSION['email'])) {
                echo '
                <!-- Login Button --> 
                <form action="landing_page.html" method="GET">
                    <input type="hidden" name="redirect" value="' . htmlspecialchars($current_url) . '">
                    <input class="modal-login-btn3" type="submit" value="Login to book meeting">
                </form>';
            } else {
                if ($_SESSION['email'] == $mEmail) {
                    // Cannot book/request a meeting if member login is the same as host of meeting
                    echo '
                    <div class="buttons">
                        <input class="unable-btn" type="button" value="Unable to Book" onclick="bookReqOwn()">
                    </div>
                    ';
                } else {
                    $requrl = 'url.php?url=' . urlencode($url) . '&Page=Req';
                    echo '
                    <div class="buttons">
                        <!-- Conditionally Render "Book Meeting" Button -->
                        ' . ($hasSlots ? '<input class="modal-login-btn" type="button" onclick="submitBooking()" value="Book meeting">' : '') . '
                        
                        <!-- "Request an Alternate Time" Button Always Visible -->
                        <input class="modal-login-btn2" type="button" onclick="window.location.href=\'' . htmlspecialchars($requrl) . '\'" value="Request an alternate time">
                    </div>';
                }

            }
            echo '</div></div>';
        } else if (isset($_GET["Page"]) && $_GET["Page"] == "Req") {
            echo '
            <!-- Next Available Block -->
            <form action="requestAlt.php" method="POST" id="reqBook">
                <div class="next-available">
                    <div class="next-av">Select date and times:</div>
                    <div class="date">Date: &nbsp;
                        <input type="date" name="newDate" placeholder="YYYY-MM-DD" style="margin-right: 10px;" required />
                    </div>
                    <div class="time">Time: &nbsp;
                        <input type="time" name="newTime" placeholder="HH:MM" required />
                    </div>
                    <input type="hidden" name="urlToUse" value="' . htmlspecialchars($url) . '">
                </div>
                <input type="hidden" name="mEmail" value="' . htmlspecialchars($mEmail) . '">
            </form>';

            if ($_SESSION['email'] == $mEmail) {
                echo '
                    <input class="unable-btn" type="button" onclick="reqOwn()" value="Unable to request">
                ';
            } else {
                echo '
                    <input class="modal-login-btn" type="button" onclick="submitRequest()" value="Submit request">
                ';
            }
        }

        // Include JavaScript and Footer
        include('urljs.txt');
        include('footer.txt');
    }
} else {
    // URL does not exist
    http_response_code(400);
    echo '400 - Bad Request - Invalid URL';
}

// Close all prepared statements
if (isset($stmt1)) {
    $stmt1->close();
}
if (isset($stmt2)) {
    $stmt2->close();
}
if (isset($stmt3)) {
    $stmt3->close();
}

// Close the database connection
$conn->close();
?>
