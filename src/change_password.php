<?php
// Written by Melody Wang
session_start();
if (!isset($_SESSION['email'])) {
    // Return to landing_page if not logged in
    header('Location: landing_page.html?login=false');
    exit;
}

$email = $_SESSION['email'];
$is_member = $_SESSION['is_member'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = new mysqli("mysql.cs.mcgill.ca", "comp307-jgan10", "0eAjoerfZhJHYer", "fall2024-comp307-jgan10");
    if ($conn->connect_error) {
        die("Internal Server Error: " . $conn->connect_error);
    }

    // Fetch form inputs
    $current_password = isset($_POST['current-password']) ? $_POST['current-password'] : '';
    $new_password = isset($_POST['new-password']) ? $_POST['new-password'] : '';
    $confirm_password = isset($_POST['confirm-password']) ? $_POST['confirm-password'] : '';

    // Validate form inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        die("Please fill in all required fields.");
    }

    if ($new_password !== $confirm_password) {
        die("New password and confirm password do not match.");
    }

    // Check if the current password matches the one in the `user` table
    $sqlValid = "SELECT pass FROM user WHERE uEmail='$email'";
    $result = $conn->query($sqlValid);

    $warning_message = '';

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['pass'] !== $current_password) {
            $warning_message = "Current password is incorrect.";
        }
    } else {
        die("User not found in the `user` table.");
    }

    // If there is a warning message, redirect back with the message
    if (!empty($warning_message)) {
        header("Location: settings.php?warning=" . urlencode($warning_message));
        exit();
    }
    // Update password in the `user` table
    $sqlUpdateUser = "UPDATE user SET pass='$new_password' WHERE uEmail='$email'";
    if ($conn->query($sqlUpdateUser) !== TRUE) {
        echo "Error updating password in the `user` table: " . $conn->error;
        $conn->close();
        exit();
    }

    // Update password in the `settings` table
    $sqlUpdateSettings = "UPDATE settings SET pass='$new_password' WHERE uEmail='$email'";
    if ($conn->query($sqlUpdateSettings) !== TRUE) {
        echo "Error updating password in the `settings` table: " . $conn->error;
        $conn->close();
        exit();
    }

    // Update password in the `member` table (if the user is a member)
    if ($is_member) {
        $sqlUpdateMember = "UPDATE member SET pass='$new_password' WHERE mEmail='$email'";
        if ($conn->query($sqlUpdateMember) !== TRUE) {
            echo "Error updating password in the `member` table: " . $conn->error;
            $conn->close();
            exit();
        }
    }

    // Redirect to the settings page with a success message
    header("Location: settings.php?message=Password changed successfully");
    exit();

    // Close the database connection
    $conn->close();
}
?>
