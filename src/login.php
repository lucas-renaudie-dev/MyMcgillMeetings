<?php
// Written by Ping-Chieh Tu
session_start();
if (TRUE || $_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower($_POST["email"]);
    $pass = isset($_POST["password"]) ? $_POST["password"] : '';

    // remove leading/trailing space of email in case of user mistyped
    $email = trim($email);

    if (empty($_POST["redirect"])) {
        $redirect = 'index.php';
    } else {
        $redirect = $_POST["redirect"];
    }

    $is_member;
    if (str_ends_with($email, '@mail.mcgill.ca') || str_ends_with($email, '@mcgill.ca')) {
        $is_member = TRUE;
    } else {
        $is_member = FALSE;
    }

    if (empty($email) || empty($pass)) {
        die("Please fill in all required fields.");
    }

    // Load your .env
    loadEnvFile(__DIR__ . '/.env');

// Now you have $_ENV['DB_SERVERNAME'], etc.
    $servername = $_ENV['DB_SERVERNAME'];
    $dbname     = $_ENV['DB_NAME'];
    $username   = $_ENV['DB_USERNAME'];
    $password   = $_ENV['DB_PASSWORD'];

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // check if user in db
    $checkInDB = "SELECT `pass` FROM `user` WHERE `uEmail` = '$email'";
    $result = $conn->query($checkInDB);
    if ($result->num_rows > 0) {
        // found user in db
        while ($row = $result->fetch_assoc()) {
            if ($row['pass'] === $pass) {
                // direct to user private page
                $_SESSION['email'] = $email;
                $_SESSION['is_member'] = $is_member;
                header("Location: $redirect");
                exit;
            } else {
                $conn->close();
                header('Location: landing_page.html?pass=false');
                exit;
            }
        }
    } else {
        // user not found in db
        $conn->close();
        header('Location: landing_page.html?registered=false');
        exit;
    }

}

function loadEnvFile($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip lines that start with # (comments)
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Split on the first '=' character
        list($key, $value) = explode('=', $line, 2);

        // Trim whitespace
        $key   = trim($key);
        $value = trim($value);

        // Store in $_ENV so you can call $_ENV['DB_SERVERNAME']
        $_ENV[$key] = $value;
    }
}

?>