<?php
// Written by Melody Wang
session_start(); // Start session to access logged-in user information

$email = $_SESSION["email"];
$is_member = $_SESSION["is_member"];

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

// Ensure user is logged in
/*if (!isset($_SESSION['email'])) {
    die("No user logged in. Please log in first.");
}*/

$email = $_SESSION['email'];

// Fetch the user's profile picture
$sql = "SELECT pfp FROM settings WHERE uEmail = '$email'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $profilePic = !empty($row['pfp']) ? $row['pfp'] : './utils/images/useful/default-profile.png';
} else {
    $profilePic = './utils/images/useful/default-profile.png'; // Default picture if no record found
}

$conn->close();
include('header.txt');
include('settingscss.txt');
include('head.txt');
if (!isset($_SESSION["email"])) {
    include('menu-not-logged-in.txt');
} else {
    if ($_SESSION["is_member"]) {
        include('menu-logged-in-member.txt');
    } else {    
        include('menu-logged-in-user.txt');
    }
}

?>

<div class="main-content">
    <h1>Settings</h1>
    <div class="settings-section">
        <h2>Profile Picture</h2>
        <!-- Current Profile Picture -->
        <div class="profile-preview">
            <img id="profile-pic" src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture">
        </div>
        <!-- Upload New Picture -->
        <form id="update-profile-pic-form" action="update_profile_pic.php" method="POST" enctype="multipart/form-data">
            <input type="file" id="profile-pic-upload" name="profile-pic" accept="image/*" required>
            <button type="submit">Save</button>
        </form>
        <br>
        <h2>Change Password</h2>
        <form id="change-password-form" action="change_password.php" method="POST">
            <label for="current-password">Current Password:</label>
            <input type="password" id="current-password" name="current-password" placeholder="Enter current password" required>
            <br>
            <label for="new-password">New Password:</label>
            <input type="password" id="new-password" name="new-password" placeholder="Enter new password" required>
            <br>
            <label for="confirm-password">Confirm New Password:</label>
            <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm new password" required>
            <button type="submit">Update Password</button>
        </form>
        <?php if (isset($_GET['warning'])): ?>
    <div class="warning-message" style="color: red; font-weight: bold; margin-bottom: 20px;">
        <?php echo htmlspecialchars($_GET['warning']); ?>
    </div>
<?php endif; ?>
        <?php if (isset($_GET['message'])): ?>
    <div class="success-message" style="color: green; font-weight: bold; margin-bottom: 20px;">
        <?php echo htmlspecialchars($_GET['message']); ?>
    </div>
<?php endif; ?>
    </div>
</div>
<?php
include('settingsjs.txt');
include ('footer.txt')
?>
</body>
</html>


