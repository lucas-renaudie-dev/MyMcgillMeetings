<?php
// Written by Jeffrey Gan
//session_start();

if (!isset($_SESSION['email'])) {
    // return to landing_page if not logged in
    header('Location: landing_page.html?login=false');
    exit;
}

$email = $_SESSION['email'];
$is_member = $_SESSION['is_member'];

include("delete-links/delete-manage-link");

if ($is_member == FALSE) {
    // return bad request if not member
    echo "400: Bad Request";
    exit;
}

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

$booking_urls_sql = 
    "SELECT 
    url, mEmail, title, description, startdate, enddate
    FROM booking
    WHERE mEmail = ?
    ORDER BY startdate ASC
    ";

$urls = array();

$urls_stmt = $conn->prepare($booking_urls_sql);
$urls_stmt->bind_param("s", $email);
$urls_stmt->execute();
$result = $urls_stmt->get_result();
if ($result->num_rows > 0) {
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $bookings = array();
}
$urls_stmt->close();
$conn->close();

// load page
include('ManageBookingsCSS.txt');
include('ManageBookingsMainContent.txt');
?>
<script>
    const bookingData = <?php echo json_encode($bookings); ?>;
</script>
<?php
include('ManageBookingsJs.txt');
//include('footer.txt');
?>

