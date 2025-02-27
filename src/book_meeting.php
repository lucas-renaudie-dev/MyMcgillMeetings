<?php
// Written by Ping-Chieh Tu
session_start();
$email = $_SESSION["email"];
$is_member = $_SESSION["is_member"];

include('header.txt');
include('head.txt');
if ($is_member) {
    include('menu-logged-in-member.txt');
} else {
    include('menu-logged-in-user.txt');
}

$url = $_POST["url"];
$slotId = $_POST["bookingTime"];

if (empty($slotId)) {
    header("Location: enter_date_time_failed.php");
    exit;
}

$servername = "mysql.cs.mcgill.ca";
$dbname = "fall2024-comp307-jgan10";
$username = "comp307-jgan10";
$password = "0eAjoerfZhJHYer";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "UPDATE `bookingslot` SET `uEmail` = '$email' WHERE `url` = '$url' AND `slotId` = '$slotId'";
$result = $conn->query($sql);

if ($result == TRUE) {
    // Retrieve meeting and slot details
    $bookingDetails = [];

    $sql = "SELECT * FROM `booking` WHERE `url` = '$url'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookingDetails['title'] = $row['title'];
            $bookingDetails['description'] = $row['description'];
            $bookingDetails['type'] = $row['type'];
            $bookingDetails['mEmail'] = $row['mEmail'];
        }
    }

    $sql = "SELECT * FROM `member` WHERE `mEmail` = '{$bookingDetails['mEmail']}'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookingDetails['host_fname'] = $row['fname'];
            $bookingDetails['host_lname'] = $row['lname'];
        }
    }

    $sql = "SELECT * FROM `bookingslot` WHERE `slotId` = '$slotId'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookingDetails['date'] = $row['date'];
            $bookingDetails['time'] = $row['time'];
        }
    }

    // Redirect to booking_success.php with the details
    $_SESSION['booking_details'] = $bookingDetails;
    header("Location: booking_success.php");
    exit;

} else {
    header("Location: book_meeting_failed.php");
    exit;
}

include('footer.txt');
$conn->close();
?>
