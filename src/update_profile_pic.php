<?php
// Written by Melody Wang
session_start(); // Start session to access logged-in user information
// Database credentials
$host = "mysql.cs.mcgill.ca";
$username = "comp307-jgan10";
$password = "0eAjoerfZhJHYer";
$database = "fall2024-comp307-jgan10";

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check for connection errors
if ($conn->connect_error) {
    die("Internal Server Error: " . $conn->connect_error);
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve the logged-in user's email (session variable)
   /* if (!isset($_SESSION['email'])) {
        die("No user logged in. Please log in to update your profile picture.");
    }*/
}
   $email = $_SESSION['email'];
  // $email = 'melody.wang@mail.mcgill.ca'; // Hardcoded email for testing purposes

    // Get the uploaded file information
    $file = $_FILES['profile-pic'];

    // Check if a file was uploaded without errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Ensure uploads directory exists
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }

        // Generate a unique filename for the uploaded file
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid("profile_", true) . "." . $fileExtension;
        $filePath = $uploadDir . $fileName;

        // Move the file to the uploads directory
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Update the database
            $sqlUpdate = "UPDATE settings SET pfp = '$filePath' WHERE uEmail = '$email'";
            if ($conn->query($sqlUpdate) === TRUE) {
                // Redirect to the settings page with the updated profile picture
                header("Location: settings.php");
                exit();
            } else {
                die("Error updating profile picture in the database.");
            }
        } else {
            die("Error moving the uploaded file.");
        }
    } else {
        die("No file uploaded or an error occurred.");
    }
            
// Close the database connection
$conn->close();
?>
