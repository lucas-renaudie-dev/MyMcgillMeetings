<?php
// Written by Lucas Renaudie
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My McGill Meetings</title>
    <link rel="stylesheet" href="dashboard_template.css">
    <link rel="icon" type="image/x-icon" href="./utils/images/useful/favicon.ico">
    <style>
        /* Add this to your existing <style> block */
        /* Success Container Styling */
        .main-content {
            display: flex;
            height: 700px;
            align-items: center;
            justify-content: center;
        }

        .booking-success {
            width: 560px;
            height: 210px;
            background-color: #FFE4E1; /* Light green background */
            color: #DC143C; /* Darker green text */
            padding: 20px;
            border-radius: 40px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.15);
            transition: padding-bottom 0.15s ease; /* Smooth transition */
            text-align: center; /* Center align text and elements */
        }

        /* Success Icon Styling */
        .booking-success .success-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
        }

        /* Success Message Styling */
        .booking-success .success-message {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .buttons {
            margin-top: 60px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-around;
        }

        /* Return to Home Button Styling */
        .booking-success .return-button {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 10px;
            padding-left: 60px;
            padding-right: 60px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer; /* Ensure pointer cursor */
            font-size: 18px;
            transition: background-color 0.3s ease;
        }
        .booking-success .return-button:hover {
            background-color: #e60000;
        }
        .booking-success .return-button:active {
            background-color: #cc0000;
        }
        .booking-success .copy-button {
            background-color: #aaa;
            color: white;
            border: none;
            padding: 10px;
            padding-left: 59px;
            padding-right: 59px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer; /* Ensure pointer cursor */
            font-size: 18px;
            transition: background-color 0.3s ease;
        }
        .booking-success .copy-button:hover {
            background-color: #888;
        }
        .booking-success .copy-button:active {
            background-color: #666;
        }

        .check-icon {
            height: 40px;
            width: auto;
        }

        @media (max-width: 768px) { /* Mobile */
            .booking-success {
                width: 300px;
                height: 300px;
                padding: 25px;
            }
            .booking-success .success-message {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 20px;
                margin-top: 20px;
            }

            .buttons {
                margin-top: 60px;
                display: flex;
                flex-direction: column;
            }
            .copy {
                margin-bottom: 40px
            }
        }
    </style>
</head>
<body>
<?php
include('header.txt');
include('head.txt');
// Include Menu Based on Session
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
    <!-- Success Container -->
    <div class="booking-success">
        <!-- Success Icon (Check Mark) -->

        <!-- Success Message -->
        <div class="success-message">Please enter a date and time in the future !</div>

        <div class="check">
            <img class="check-icon" src="utils/images/useful/red-x.png">
        </div>

        <div class="buttons">
            <div class="copy">
                <a href='javascript:history.back()' class="copy-button">Back to meeting</a>
            </div>
            <!-- Return to Home Button -->
            <div class="return">
                <a href='index.php?Page=Home' class="return-button">Return to Home</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include Footer and JavaScript
include('footer.txt');
?>
</body>
</html>
