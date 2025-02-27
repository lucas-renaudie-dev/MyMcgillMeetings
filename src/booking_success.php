<?php
// Written by Lucas Renaudie
session_start();
if (!isset($_SESSION['booking_details'])) {
    header("Location: index.php");
    exit;
}

$bookingDetails = $_SESSION['booking_details'];

include('header.txt');
?>

<style>
    /* Success Container Styling */
    .main-content {
        display: flex;
        height: 760px;
        align-items: center;
        justify-content: center;
    }

    .booking-success {
        width: 560px;
        height: 480px;
        background-color: #d4edda; /* Light green background */
        color: #155724; /* Darker green text */
        padding: 20px;
        border-radius: 40px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.15);
        transition: padding-bottom 0.15s ease; /* Smooth transition */

        /* Flexbox to center child elements */
        display: flex;
        flex-direction: column;
        align-items: center; /* Centers child elements horizontally */
        justify-content: center; /* Centers child elements vertically */

        /* Remove text-align to allow individual alignment */
        text-align: left;
    }

    .booking-details {
        /* Center the div itself within the flex container */
        align-self: center; /* Centers the div horizontally within the flex container */

        /* Left-align text inside booking-details */
        text-align: left;

        /* Optional: Define a max-width for better readability */
        max-width: 70%;

        /* Adjust margins for better spacing */
        margin-top: 40px;
        margin-bottom: 0px;

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
        text-align: center; /* Center align the message */
    }

    /* Buttons Container Styling */
    .buttons {
        margin-top: 60px;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center; /* Center buttons horizontally */
        gap: 20px; /* Space between buttons */
    }

    /* General Button Styling */
    .booking-success .copy-button {
        color: white;
        border: none;
        padding: 10px 50px; /* Uniform padding */
        text-align: center;
        border-radius: 5px;
        cursor: pointer; /* Ensure pointer cursor */
        font-size: 18px;
        transition: background-color 0.3s ease;
        text-decoration: none; /* Remove underline from links */
        display: inline-block; /* Ensure proper spacing */
    }

    .booking-success .return-button  {
        color: white;
        border: none;
        padding: 10px 60px; /* Uniform padding */
        text-align: center;
        border-radius: 5px;
        cursor: pointer; /* Ensure pointer cursor */
        font-size: 18px;
        transition: background-color 0.3s ease;
        text-decoration: none; /* Remove underline from links */
        display: inline-block; /* Ensure proper spacing */
    }


    /* Specific Button Colors */
    .booking-success .return-button {
        background-color: #ff4d4d; /* Red */
    }

    .booking-success .copy-button {
        background-color: #aaa; /* Gray */
    }

    /* Hover Effects */
    .booking-success .return-button:hover {
        background-color: #e60000;
    }

    .booking-success .copy-button:hover {
        background-color: #888;
    }

    /* Active Effects */
    .booking-success .return-button:active {
        background-color: #cc0000;
    }

    .booking-success .copy-button:active {
        background-color: #666;
    }

    /* No Slots Message Styling */
    .no-slots-message {
        color: #ff0000; /* Red color for emphasis */
        font-weight: bold;
        text-align: center;
        margin-top: 10px;
    }

    /* Check Icon Styling */
    .check-icon {
        height: 40px;
        width: auto;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) { /* Mobile */
        .booking-success {
            width: 300px;
            height: 520px;
            padding: 25px;
        }
        .booking-success .success-message {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .buttons {
            flex-direction: column; /* Stack buttons vertically on mobile */
        }
        .copy {
            margin-bottom: 10px;
        }

        /* Adjust button widths for mobile */
        .booking-success .copy-button {
            padding: 10px 30px; /* Reduced padding on mobile */
            width: auto; /* Changed from 100% to auto */
            align-self: center; /* Centers the button within the flex container */
        }

        .booking-success .return-button {
            padding: 10px 40px; /* Reduced padding on mobile */
            width: auto; /* Changed from 100% to auto */
            align-self: center; /* Centers the button within the flex container */
        }



        /* Adjust booking-details margins */
        .booking-details {
            margin-top: 30px;
            margin-bottom: -10px;
        }
    }
</style>

<?php
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

/**
 * Truncates a given text to a specified length and appends an ellipsis if necessary.
 *
 * @param string $text The text to truncate.
 * @param int $limit The maximum number of characters to display.
 * @return string The truncated text with an ellipsis if it exceeds the limit.
 */
function truncateText($text, $limit = 100)
{
    // Check if the text length exceeds the limit
    if (mb_strlen($text) > $limit) {
        // Truncate the text to the limit
        $truncated = mb_substr($text, 0, $limit);

        // Optionally, avoid cutting words in half by trimming to the last space
        $lastSpace = mb_strrpos($truncated, ' ');
        if ($lastSpace !== false) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        // Append ellipsis
        return $truncated . '...';
    } else {
        // Return the original text if within the limit
        return $text;
    }
}

function formatDate($date_str)
{
    $date = DateTime::createFromFormat('Y-m-d', $date_str);
    if ($date && $date->format('Y-m-d') === $date_str) {
        return $date->format('M d, Y'); // e.g., "Dec 20, 2024"
    } else {
        return "Invalid Date";
    }
}

/**
 * Formats a time string from 'H:i:s' or 'H:i' to 'h:i A' (e.g., "10:32 AM").
 *
 * @param string $time_str The time string in 'H:i:s' or 'H:i' format.
 * @return string The formatted time string or "Invalid Time" if parsing fails.
 */
function formatTime($time_str)
{
    // Attempt to parse time with seconds
    $time = DateTime::createFromFormat('H:i:s', $time_str);
    if ($time && $time->format('H:i:s') === $time_str) {
        return $time->format('h:i A'); // e.g., "10:32 AM"
    }

    // Attempt to parse time without seconds
    $time = DateTime::createFromFormat('H:i', $time_str);
    if ($time && $time->format('H:i') === $time_str) {
        return $time->format('h:i A'); // e.g., "10:32 AM"
    }

    return "Invalid Time";
}
?>

<div class="main-content">
    <!-- Success Container -->
    <div class="booking-success" <?php echo (empty($bookingDetails['description']) ? 'style="padding-top:0; padding-bottom:0;"' : ''); ?>>

        <!-- Success Message -->
        <div class="success-message">Booking successful!</div>

        <!-- Success Icon (Check Mark) -->
        <div class="check">
            <img class="check-icon" src="utils/images/useful/check-icon.png" alt="Success Icon">
        </div>

        <!-- Booking Details -->
        <div class="booking-details">
            <p><strong>Host:</strong> <?php echo htmlspecialchars(truncateText($bookingDetails['host_fname'] . ' ' . $bookingDetails['host_lname'], 28)); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars(formatDate($bookingDetails['date'])); ?></p>
            <p><strong>Time:</strong> <?php echo htmlspecialchars(formatTime($bookingDetails['time'])); ?></p>
            <p><strong>Meeting:</strong> <?php echo htmlspecialchars(truncateText($bookingDetails['title'], 25)); ?></p>
            <?php
            echo '
            <div ' . (empty($bookingDetails['description']) ? 'style="margin-bottom: 0px;"' : '') . '>
                <p>' . (empty($bookingDetails['description']) ? '' : '<strong>Description:</strong> ' . htmlspecialchars(truncateText($bookingDetails['description'], 25))) . '</p>
            </div> ';
            ?>

        </div>

        <!-- Buttons -->
        <div class="buttons">
            <div class="copy">
                <a href='javascript:history.back()' class="copy-button">Book another time</a>
            </div>
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
