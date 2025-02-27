<?php
// Written by Jeffrey Gan
// This is its own page
session_start();

if (!isset($_SESSION['email'])) {
    // return to landing_page if not logged in
    header('Location: landing_page.html?login=false');
    exit;
}

$email = $_SESSION['email'];
$is_member = $_SESSION['is_member'];

if ($is_member == FALSE) {
    // return bad request if not member
    echo "400: Bad Request";
    exit;
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    //$data = htmlspecialchars($data);
    return $data;
}

// Load page header to nav bar
include("header.txt");
include("head.txt");
include("menu-logged-in-member.txt");

$url = test_input($_GET["url"]);

// Database connection
$servername = "mysql.cs.mcgill.ca";
$username = "comp307-jgan10";
$password = "0eAjoerfZhJHYer"; // Use your password
$database = "fall2024-comp307-jgan10";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo '500: Internal Server Error - Database Connection Failed';
    exit;
}

// get this url's slots while verifying that logged in user is the creator
$slots_query = 
    "SELECT 
        bookingslot.url,
        bookingslot.slotId,
        bookingslot.date,
        bookingslot.time,
        bookingslot.uEmail,
        booking.mEmail,
        booking.title
    FROM 
        bookingslot
    INNER JOIN booking
        ON booking.url = bookingslot.url
    INNER JOIN member
        ON booking.mEmail = member.mEmail
    WHERE 
        bookingslot.url = ? AND booking.mEmail = ?
    ORDER BY 
        bookingslot.date ASC, bookingslot.time ASC
";
$stmt = $conn->prepare($slots_query);
$stmt->bind_param('ss', $url, $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $slots = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $slots = array();
}
$stmt->close();
$conn->close();

if(count($slots) == 0) {
    echo '<h1> No meeting times found for this booking </h1>';
    header('Location: index.php');
    exit;
}

$title = $slots[0]["title"];

include("ManageBookingSlotsCss.txt");

?>

<div class="main-content">

<h1> Manage availabilities for <?php echo $url ?> </h1>
<h2> Title: <?php echo $title ?> </h2>
<p> If someone has already booked a meeting, please make arrangements with them. You will not be able to cancel their meeting on this page. </p>
<table id="slots-table">
    <tr>
        <th class="col-id"> ID </th>
        <th class="col-date"> Date </th>
        <th class="col-time"> Time </th>
        <th class="col-avail"> Availibility </th>
        <th class="col-toggle"> </th>
    </tr>

<?php // make table rows
    $id = 0;
    $color = 0;
    foreach($slots as $s) {
        $class_color = "color" . $color;

        echo '<tr id="row-' . $id . '">';

        echo '<td class="col-id '.$class_color.'">' . $s["slotId"] . '</td>';
        echo '<td class="col-date '.$class_color.'">' . $s["date"] . '</td>';
        echo '<td class="col-time '.$class_color.'">' . $s["time"] . '</td>';

        $uEmail = $s["uEmail"];
        if ($uEmail == NULL) {
            echo '<td class="col-avail '.$class_color.'"> <span class="available" id="label-'.$id.'">AVAILABLE</span> </td>';
            echo '
            <td class="col-toggle">
                <button type="button" class="avail-toggle" id="button-'.$id.'" onclick="toggleAvailability('.$id.')"> Toggle </button>
            </td>'; 
        } else if ($uEmail == $email) {
            echo '<td class="col-avail '.$class_color.'"> <span class="unavailable" id="label-'.$id.'">UNAVAILABLE</span> </td>';
            echo '
            <td class="col-toggle">
                <button type="button" class="avail-toggle" id="button-'.$id.'" onclick="toggleAvailability('.$id.')"> Toggle </button>
            </td>'; 
        } else {
            echo '<td class="col-avail '.$class_color.'"> <span class="booked"> Booked by: '. $uEmail .'</span> </td>';
            echo '<td class="col-toggle"> <button type="button" class="avail-toggle" onclick="" disabled> Toggle </button> </td>'; 
        }

        echo '</tr>';

        $id++;
        $color = 1 - $color;
    }
?>
</table>
</div>

<script>
    const thisEmail = <?php echo '"'.$email.'"'; ?>;
    const thisUrl = <?php echo '"'.$url.'"'; ?>;
    const data = <?php echo json_encode($slots); ?>;

    function toggleAvailability(id) {
        const thisSlotId = data[id].slotId;
        const label = document.getElementById("label-" + id);
        
        let val = label.innerText;
        console.log(id);
        console.log(val);
        if (val == "AVAILABLE") { // make appointment unavailable
            fetch('UnavailableAppointment.php', {
                method: 'POST',
                body: JSON.stringify({ email: thisEmail, url: thisUrl, slotId: thisSlotId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success == true) {
                    label.innerText = "UNAVAILABLE";
                    label.classList.remove("available");
                    label.classList.add("unavailable");
                }
            })
            .catch(error => console.log(error));
            
        } else { // make appointment available
            label.innerText = "AVAILABLE";
            fetch('CancelAppointment.php', {
                method: 'POST',
                body: JSON.stringify({ url: thisUrl, slotId: thisSlotId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success == true) {
                    label.innerText = "AVAILABLE";
                    label.classList.remove("unavailable");
                    label.classList.add("available");
                }
            })
            .catch(error => console.log(error));
        }
    }

</script>

<?php
    include("footer.txt");
?>