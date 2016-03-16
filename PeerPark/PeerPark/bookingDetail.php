<?php 
/**
 * Web page to  see the detail of the booking
 */
require_once('include/common.php');
require_once('include/database.php');
startValidSession();
htmlHead();
?>
<h1>Booking Detail</h1>
<?php
	
	$book = getBookingInfo($_REQUEST['bookingID']);

?>
    <form action="bookingDetail.php" id="bookingDetail" method="post">
	    <label>Booking ID<input readonly="readonly" name="bookingID"  value="<?php echo $book['bookingID'];?>"/></label><br />		
        <label>Bay Location <input readonly="readonly" name="bayLocation" value="<?php echo $book['bayLocation'];?>"/></label><br />
        <label>Car <input readonly="readonly" name="car" value="<?php echo $book['car'];?>"/></label><br />
		<label>Member Name <input readonly="readonly" name="memberName"  value="<?php echo $book['memberName'];?>"/></label><br />
		<label>Booking Date<input readonly="readonly" name="bookingDate" value="<?php echo $book['bookingDate'];?>"/></label><br />
		<label>Booking Hour<input readonly="readonly"  name="bookingHour" value="<?php echo $book['bookingHour'];?>"/></label><br/>		
		<label>Duration<input readonly="readonly" name="duration"  value="<?php echo $book['duration'];?>"/></label><br />	
		
    </form>
<?php

htmlFoot();
 ?>