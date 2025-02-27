<?php
// Written by Lucas Renaudie
ob_start();

session_start();
$email = $_SESSION["email"];
$is_member = $_SESSION["is_member"];

include('reqcss.txt');
$mEmail = $_POST["mEmail"];
$newDate = $_POST["newDate"];
$newTime = $_POST["newTime"];
if (empty($newDate) || empty($newTime)) {
    header("Location: enter_date_time_failed.php");
    exit;
}

$dateTimeString = $newDate . ' ' . $newTime;

$desiredDateTime = new DateTime($dateTimeString);
$currentDateTime = new DateTime();

if ($desiredDateTime < $currentDateTime) {
    header("Location: enter_date_time_future_failed.php");
    exit;
}

$current_url = $_POST["urlToUse"];

// After input validation, get booking info from db
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

// create a requestId and a reqslotId
// requestId and rslotId both 11 digits 
$requestId = rand(0, 2147483647);
$sql = "SELECT * FROM `request` WHERE `requestId` = '$requestId'";
$result = $conn->query($sql);
while ($result->num_rows > 0) {
    $requestId = rand(0, 2147483647);
    $sql = "SELECT * FROM `request` WHERE `requestId` = '$requestId'";
    $result = $conn->query($sql);
}
// now $requestId will be unique

$rslotId = rand(0, 2147483647);
$sql = "SELECT * FROM `requestslot` WHERE `requestId` = '$requestId' AND `rslotId` = '$rslotId'";
$result = $conn->query($sql);
while ($result->num_rows > 0) { 
    $rslotId = rand(0, 2147483647);
    $sql = "SELECT * FROM `request` WHERE `requestId` = '$requestId' AND `rslotId` = '$rslotId'";
    $result = $conn->query($sql);
}
// now $rslotId will be unique within request id

$sql1 = $conn->prepare("INSERT INTO `request`(`requestId`, `mEmail`, `uEmail`, `url`) VALUES (?, ?, ?, ?)");
$sql1->bind_param("ssss", $requestId, $mEmail, $email, $current_url);
$result1 = $sql1->execute();


$sql2 = $conn->prepare("INSERT INTO `requestslot`(`requestId`, `rslotId`, `date`, `time`) VALUES (?, ?, ?, ?)");
$sql2->bind_param("siss", $requestId, $rslotId, $newDate, $newTime);
$result2 = $sql2->execute();
if (!$result1) {
    die("Error executing query for `request`: " . $sql1->error);
}
if (!$result2) {
    die("Error executing query for `requestslot`: " . $sql2->error);
}

if ($result1 != TRUE || $result2 != TRUE) {
    // error message
    header("Location: request_failed.php");
    exit();
} else {
    header("Location: request_alternate_success.php");
    exit();
}
$conn->close();
ob_end_flush();

?>
