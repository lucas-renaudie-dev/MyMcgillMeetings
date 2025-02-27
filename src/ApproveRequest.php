<?php
//written by danielle rhodes
$servername = "mysql.cs.mcgill.ca";
$dbname = "fall2024-comp307-jgan10";
$username = "comp307-jgan10";
$password = "0eAjoerfZhJHYer";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sqlSlot = "SELECT MAX(slotid) AS highestSlotId FROM bookingslot";
$result = $conn->query($sqlSlot);
$row = $result->fetch_assoc(); 
$highestSlotId = $row['highestSlotId'];
// Check if there is no highestSlotId
if ($highestSlotId === null) {
    $slotId = 1; // If no records, start from 1
} else {
    $slotId = $highestSlotId + 1; // Increment the highest slotid by 1
}
if (isset($_POST['requestId']) && isset($_POST['url']) && isset($_POST['date'])&& isset($_POST['time'])&& isset($_POST['uEmail'])) {
    $requestId = $_POST['requestId'];
    $url = $_POST['url'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $uEmail = $_POST['uEmail'];
   
}

$sql = "INSERT INTO bookingslot (url, date, time, uEmail, slotId) VALUES (?, ?, ?, ?, ?)";

$query = $conn->prepare($sql);
$query->bind_param("ssssi", $url, $date, $time, $uEmail, $slotId);


$sql2 = "DELETE FROM request WHERE requestId = ?";
$stmt = $conn->prepare($sql2);
$stmt->bind_param("s", $requestId);
$stmt->execute();
if ($query->execute()) {
    echo json_encode(['success' => true, 'message' => 'Request approved successfully! ']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to approve request']);
}

$query->close();
$conn->close();
?>
