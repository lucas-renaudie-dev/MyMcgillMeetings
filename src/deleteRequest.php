<?php
//written by danielle rhodes
$servername = "mysql.cs.mcgill.ca";
$dbname = "fall2024-comp307-jgan10";
$username = "comp307-jgan10";
$password = "0eAjoerfZhJHYer";

$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$requestId = $_POST['requestId'] ?? null;
if (!$requestId) {
    echo json_encode(['success' => false, 'message' => 'Request ID is missing']);
    exit;
}

$sql = "DELETE FROM request WHERE requestId = ?";
$query = $conn->prepare($sql);
$query->bind_param("s", $requestId);

if ($query->execute()) {
    echo json_encode(['success' => true, 'message' => 'Request deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete request']);
}

$query->close();
$conn->close();
?>
