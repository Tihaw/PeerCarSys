<?php
/**
 * Home page giving details of a specific member
 */
require_once('include/common.php');
require_once('include/database.php');
startValidSession();
htmlHead();
?>
<h1>Home</h1>
<?php
try {
    $details = getUserDetails($_SESSION['memberNo']);
    echo '<h2>memberNo</h2> ',$details['memberNo'];
    echo '<h2>Name</h2> ',$details['name'];
    echo '<h2>eMail</h2> ',$details['email'];
    echo '<h2>Address</h2> ',$details['address'];
    echo '<h2>Preferred Bay</h2>',$details['prefBay'],' - ',$details['prefBayName'];
    echo '<h2>Preferred Billing </h2>',$details['prefBillingNo'],' - ',$details['prefBillingName'];
    echo '<h2>Total bookings</h2> ',$details['nbookings'];
} catch (Exception $e) {
    echo 'Cannot get user details';
}
try {
   $cars = getCars($_SESSION['memberNo']);
   if(count($cars)>0) {
		echo '<table>';
		echo '<thead>';
		echo '<tr><th>Owned Car</th></tr>';
		echo '</thead>';
		echo '<tbody>';
		foreach($cars as $car) {
			echo '<tr><td>',$car['car'],'</td></tr>';
		}
		echo '</tbody>';
		echo '</table>';
    }
} catch (Exception $e) {
    echo 'Cannot get bays status';
}
htmlFoot();
?>
