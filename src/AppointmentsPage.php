<?php
//written by danielle rhodes
if (!isset($_SESSION['email'])) {
    // return to landing_page if not logged in
    header('Location: landing_page.html?login=false');
    exit;
}

$email = $_SESSION['email'];
$is_member = $_SESSION['is_member'];

include("delete-links/delete-home-link");
include('appointmentsPageCss.txt');

// Database connection
$servername = "mysql.cs.mcgill.ca";
$username = "comp307-jgan10";
$password = "0eAjoerfZhJHYer"; 
$database = "fall2024-comp307-jgan10";

$conn = new mysqli($servername, $username, $password, $database);
$appointments = [];

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
        SELECT 
            bookingslot.url,
            bookingslot.time,
            bookingslot.uEmail,
            bookingslot.date,
            bookingslot.slotId,
            booking.title,
            booking.description,
            booking.type,
            booking.mEmail,
            user.fname AS user_fname,
            user.lname AS user_lname,
            member.fname AS member_fname,
            member.lname AS member_lname
        FROM 
            bookingslot
        INNER JOIN booking
            ON bookingslot.url = booking.url
        INNER JOIN user
            ON user.uEmail = bookingslot.uEmail
        INNER JOIN member
            ON booking.mEmail = member.mEmail
        WHERE 
            (bookingslot.uEmail = ?)
            OR (
                booking.mEmail = ?
                AND bookingslot.uEmail IS NOT NULL
                AND booking.url = bookingslot.url
            )
        ORDER BY 
            bookingslot.date ASC
    ";


    $query = $conn->prepare($sql);
    $query->bind_param("ss", $email, $email); // Both placeholders replaced by $email
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $appointments = [
            'thisUser' => $email,
            'appointments' => $rows
        ];
    }



$query->close();
$conn->close();

include('appointmentsPageMainContent.txt');
?>
<script>
    const appointmentsData = <?php echo json_encode($appointments); ?>;
</script>
<?php
include('AppointmentsPageJS.txt');
?>

