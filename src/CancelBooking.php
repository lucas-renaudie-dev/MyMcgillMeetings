<?php
// Adapted from Danielle Rhodes' CancelAppointment.php by Jeffrey Gan
header('Content-Type: application/json');
try {
    // Get raw POST data
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    $host = "mysql.cs.mcgill.ca";
    $username = "comp307-jgan10";
    $password = "0eAjoerfZhJHYer"; // Use your password
    $database = "fall2024-comp307-jgan10";

    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
        throw new Exception("DB Connection failed: " . $conn->connect_error);
    }
    $url = $data['url'];

    $query = "DELETE FROM booking WHERE url = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $url);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Booking deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No matching booking found.']);
        }
        $conn->close();
    } else {
        throw new Exception("Query execution failed: " . $stmt->error);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
