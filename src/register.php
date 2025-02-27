<?php
// Written by Ping-Chieh Tu
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = isset($_POST["reg-firstname"]) ? $_POST["reg-firstname"] : '';
    $lname = isset($_POST["reg-lastname"]) ? $_POST["reg-lastname"] : '';
    $email = isset($_POST["reg-email"]) ? $_POST["reg-email"] : '';
    $pass = isset($_POST["reg-password"]) ? $_POST["reg-password"] : '';
    $confirmpass = isset($_POST["reg-confirm-password"]) ? $_POST["reg-confirm-password"] : '';
    $phone = isset($_POST["reg-phone"]) && !empty($_POST["reg-phone"]) ? $_POST["reg-phone"] : NULL;

    // remove leading/trailing white space in fname, lname, email, phone
    $fname = trim($fname);
    $lname = trim($lname);
    $email = strtolower(trim($email));
    $phone = trim($phone);

    $is_member;

    if (str_ends_with($email, '@mail.mcgill.ca') || str_ends_with($email, '@mcgill.ca')) {
        $is_member = TRUE;
    } else {
        $is_member = FALSE;
    } 

    // Validate if required fields are filled
    if (empty($fname) || empty($lname) || empty($email) || empty($pass) || empty($confirmpass)) {
        die("Please fill in all required fields.");
    }

    // Check if passwords match
    if ($pass != $confirmpass) {
        die("Passwords do not match.");
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

    // check if user already register
    $checkInDB = "SELECT * FROM `user` WHERE `uEmail` = '$email'";
    $result = $conn->query($checkInDB);
    if ($result->num_rows > 0) {
        // if user already exist in db
        $conn->close();
        // jump to login page
        header('Location: landing_page.html?registered=true');
        exit;
    } else {
        // if user not exist in db
        if ($is_member) {
            // if member, add in member table
            $addNewMember = $conn->prepare("INSERT INTO member (mEmail, fname, lname, pass) VALUES (?, ?, ?, ?)");
            $addNewMember->bind_param("ssss", $email, $fname, $lname, $pass);
            $addNewMember->execute();
            if ($phone !== NULL) {
                $addPhone = $conn->prepare("UPDATE member SET phone = ? WHERE mEmail = ?");
                $addPhone->bind_param("ss", $phone, $email);
                $addPhone->execute();
            }
        }
        // add in user table
        $addNewUser = $conn->prepare("INSERT INTO user (uEmail, fname, lname, pass) VALUES (?, ?, ?, ?)");
        $addNewUser->bind_param("ssss", $email, $fname, $lname, $pass);
        $addNewUser->execute();
        if ($phone !== NULL) {
            $addPhone = $conn->prepare("UPDATE user SET phone = ? WHERE uEmail = ?");
            $addPhone->bind_param("ss", $phone, $email);
            $addPhone->execute();
            $sql = "UPDATE user SET phone='$phone' WHERE uEmail='$email'";
            $result = $conn->query($sql);
        }
        $addSettings = $conn->prepare("INSERT INTO settings (uEmail, pass) VALUES (?, ?)");
        $addSettings->bind_param("ss", $email, $pass);
        $addSettings->execute();
        $conn->close();
        // Jump to login after finish registration
        header('Location: landing_page.html?reg=true');
        exit;
    }
}

?>
