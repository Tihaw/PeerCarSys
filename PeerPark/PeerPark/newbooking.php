<?php 
/**
 * Web page to create a new booking
 */
require_once('include/common.php');
require_once('include/database.php');
startValidSession();
htmlHead();
?>
<h1>Make new booking</h1>
<?php
// Check whether all attributes for booking have been submitted
$submit =!empty($_REQUEST['carname']) 
	&& !empty($_REQUEST['bayID']) 
	&& !empty($_REQUEST['parkdate']) 
	&& !empty($_REQUEST['parktime'])
        && !empty($_REQUEST['duration']);
$booking = null;
	
if ($submit) {
echo 'Submitting booking.';
    try {
        $booking = makeBooking($_SESSION['memberNo'], $_REQUEST['carname'], $_REQUEST['bayID'], $_REQUEST['parkdate'], $_REQUEST['parktime'], $_REQUEST['duration']);
        if($booking['status'] == 'success') { 
            echo '<h2>Congratulations, you\'ve made a new new booking!';
			echo '<h2>Booking ID</h2> ',$booking['bookingID'];
			echo '<h2>Car</h2> ',$booking['car'];
			echo '<h2>Bay ID</h2> ',$booking['bayID'];
			echo '<h2>Date</h2> ',$booking['bookingDate'];
			echo '<h2>Hour</h2> ',$booking['bookingHour'];
			echo '<h2>Duration</h2> ',$booking['duration'];
			echo '<h2>Booking Cost</h2> ',$booking['cost'];
        } else {
            echo '<h2>Sorry, couldn\t make a booking:</h2>', $booking['status'];
        }
    } catch (Exception $e) {
            echo 'Couldn\'t submit booking. Please try again.';
    }
} else {
	echo 'Please complete all the booking details.';
}

if (!$submit || $booking==null || $booking['status'] != 'success') {
	// Supply defaults for any unset values	
	$carname = isset($_REQUEST['carname']) ? $_REQUEST['carname'] : 'Sample Car';
	$parkdate = isset($_REQUEST['parkdate']) ? $_REQUEST['parkdate'] : date("Y-m-d");
	$parktime = isset($_REQUEST['parktime']) ? $_REQUEST['parktime'] :  date("H:00:00");
	$duration = isset($_REQUEST['duration']) ? $_REQUEST['duration'] : 1;	
	$bayID = isset($_REQUEST['bayID']) ? $_REQUEST['bayID'] : 0;

?>
    <form action="newbooking.php" id="newbooking" method="post">
	    <label>Bay ID <input type="number" name="bayID" value="<?php echo $bayID;?>"/></label>e.g. 561567<br />
        <label>Car <input type="text" name="carname" value="<?php echo $carname;?>"/></label> e.g.  MyVOLVO<br />
		<label> date <input type="date" name="parkdate"  value="<?php echo $parkdate;?>"/></label><br />
		<label>time <input type="time" step="3600" name="parktime" min="00:00:00" value="<?php echo $parktime;?>"/></label><br />
		<label>Duration (hours) <input type="number" name="duration" step="1"  min="1" value="<?php echo $duration;?>"/></label><br />
		<br /><input type=submit value="Request Booking"/>
    </form>
<?php
}
htmlFoot();
?>
