<?php
/**
 * Web page to display users active bookings
 */
require_once('include/common.php');
require_once('include/database.php');
startValidSession();
htmlHead();
?>
<h1>Active Bookings</h1>
<?php
try {
    $bookings = getOpenBookings($_SESSION['memberNo']);
    echo '<table>';
    echo '<thead>';
    echo '<tr><th>Bay Location</th><th>Car</th><th>Date</th></tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach($bookings as $booking) {
        echo '<tr><td><a href="bookingDetail.php?bookingID=',$booking['bookingID'],'">',$booking['bayLocation'],'</td><td>',$booking['car'],'</td>',
                '<td>',$booking['bookingDate'],'</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
} catch (Exception $e) {
        echo 'Cannot get available bookings';
}
htmlFoot();
?>
