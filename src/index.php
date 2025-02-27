<?php
// Written by Ping-Chieh Tu
session_start();
if (!isset($_SESSION['email'])) {
    // return to landing_page if not logged in
    header('Location: landing_page.html?login=false');
    exit;
}

$email = $_SESSION['email'];
$is_member = $_SESSION['is_member'];

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

$sql = "SELECT * FROM `user` WHERE `uEmail` = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fname = $row["fname"];
        $lname = $row["lname"];
    }
}

include('header.txt');
include('head.txt');
// email and member check to display different menu bar
if ($email) {
    if ($is_member) {
        include('menu-logged-in-member.txt');
    }
    else {
        include('menu-logged-in-user.txt');
    }
}
else {
    include('menu-not-logged-in.txt');
}

// switch content for main content
if (sizeof($_GET) == 0 || $_GET["Page"] == "Home") {
    echo "<h2 class='welcome-text'>Welcome ".$fname." ".$lname."</h2>";
    include('AppointmentsPage.php');
} else if ($_GET["Page"] === "Create") {
    include('CreateBookingPage.php');
} else if ($_GET["Page"] === "Manage") {
    include('ManageBookingsPage.php');
} else if ($_GET["Page"] === "Requests") {
    include('request.php');
} else if ($_GET["Page"] == "Settings") {
    include('settings.php');
} else {
    // ERROR PAGE
    http_response_code(404);
    echo "404: Invalid Page";
}

include('footer.txt');

?>
<html>