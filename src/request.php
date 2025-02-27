<?php
//written by danielle rhodes and ping-chieh tu
if (!isset($_SESSION['email'])) {
    header('Location: landing_page.html?login=false');
    exit;
}
$email = $_SESSION['email'];
$is_member = $_SESSION['is_member'];


include("delete-links/delete-requests-link");
include("Requestcss.txt");

if ($is_member == FALSE) {
    echo "400: Bad Request";
    exit;
}

// connect to db
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

$sql = "
    SELECT 
        request.requestId,
        request.url,
        request.uEmail,
        user.fname,
        user.lname,
        requestslot.date,
        requestslot.time
    FROM 
        request
    INNER JOIN 
        user
    ON 
        user.uEmail = request.uEmail
    INNER JOIN 
        requestslot
    ON 
        requestslot.requestId = request.requestId
    WHERE 
        request.mEmail = '$email'
";

echo "<div class=\"main-content\">
    <div class=\"two-section-layout\">
        
        <!-- Right Section (Display Selected Content) -->
        <div class=\"right-section\" id=\"right-section\">
            <div class=\"selected-content\">Please select a request from the section</div>
            <div class=\"hidden-button-container\" id=\"hidden-button-container\" style=\"display: none;\">
                <button class=\"approve-button\" onclick=\"approveReq()\">Approve</button>
                <button class=\"refuse-button\" onclick=\"refuseReq()\">Refuse</button>
            </div>
        </div>
        <div class=\"left-section\" id=\"itemContainer\">
";
function formatDate($date_str) {
    $date = DateTime::createFromFormat('Y-m-d', $date_str);
    if ($date && $date->format('Y-m-d') === $date_str) {
        return $date->format('M d, Y'); // "Dec 20, 2024"
    } else {
        return "Invalid Date";
    }
}

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        $requestID = $row['requestId'];
        $uEmail = $row['uEmail'];
        $userFname = $row['fname'];
        $userLname = $row['lname'];
        $name = $userFname . " " . $userLname; // Use . for string concatenation
        $date = $row['date'];
        $formattedDate = formatDate($date);
        $time = $row['time'];
        $formattedTime = date("g:i A", strtotime($time));
        $url = $row['url'];
        $id = $counter;
        echo "
        <div id=\"$id\" class='item' 
            data-content='{\"name\":\"$name\", \"date\":\"$date\", \"time\":\"$time\", \"requestId\": \"$requestID\", \"url\": \"$url\", \"uEmail\": \"$uEmail\", \"id\": \"$id\"}' 
            >
            <p><b>Requester: $name</b></p>
            <p><b>Date & Time: $formattedDate $formattedTime</b></p>
        </div>";
        
        $counter += 1;
    }
    echo  '</div>
    </div>
</div>';
} 
else {
    echo "<h2>No requests</h2>";
}

include("RequestJs.txt");

?>
