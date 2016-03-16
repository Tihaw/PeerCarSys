<?php
/**
 * Web page to get the list of bays, defaulting to a member's preffered bay
 */
require_once('include/common.php');
require_once('include/database.php');
startValidSession();
htmlHead();
?>
<h1>Bays List</h1>
<?php
try {

	if (!empty($_REQUEST['bayAddress']) )
	  {
	   $bays = searchBay($_REQUEST['bayAddress']);
	  }
	elseif(isset($_REQUEST['all'])){
	    $bays = getBays();
	  }

	else{
	   $bays = getPrefBayInformation($_SESSION['memberNo']);
	}

?>
	<form action="baylist.php" id="baylist" method="post">
	<label>Bay Adress <input type=text name="bayAddress" /></label>
	<input type=submit value="Search" /><br /><br />
	<input type=submit name='all' value="All" />
	</form>
	<a href="?doclear=1">Clear bookings table and make all available</a>
<?php
	if(isset($_GET['doclear']))
		clearBookings();
    if(count($bays)>0) {
		echo '<table>';
		echo '<thead>';
		echo '<tr><th>Bay ID</th><th>Bay Site</th><th>Address</th><th> Available</th></tr>';
		echo '</thead>';
		echo '<tbody>';
		foreach($bays as $bay) {
			echo '<tr><td><a href="baydetail.php?bayID=',$bay['bayID'],'">',$bay['bayID'],'</td><td>',$bay['site'], '</td><td>',$bay['address'], '</td><td><a href="newbooking.php?bayID=',$bay['bayID'],'">',$bay['avail']==true ? 'yes' : 'no', '</td></tr>';
		}
		echo '</tbody>';
		echo '</table>';
    } else {
		echo '<p>No bays or invalid bays. Try another bay.</p>';
	}
} catch (Exception $e) {
    echo 'Cannot get bays status';
}

htmlFoot();
?>
