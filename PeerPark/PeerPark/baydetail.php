<?php
/**
 * Web page to  see the detail of the bay
 */
require_once('include/common.php');
require_once('include/database.php');
startValidSession();
htmlHead();
?>
<h1>Bay Detail</h1>
<?php

	$bayID = $_REQUEST['bayID'];
	$bay   = getBayInformation($bayID);

?>
    <form action="baydetail.php" id="bayDetail" method="post">
        <label>Bay ID <input readonly="readonly" name="BayID" value="<?php echo $bayID;?>"/></label><br />
        <label>Site <input readonly="readonly" name="BaySite" value="<?php echo $bay['site'];?>"/></label><br />
        <label>Description <input readonly="readonly" name="BayDescr" value="<?php echo $bay['description'];?>"/></label><br />
		<label>Address <input readonly="readonly" name="Address" value="<?php echo $bay['address'];?>"/></label><br />
		<label>Located At <input readonly="readonly" name="Location"  value="<?php echo $bay['locatedAt'];?>"/></label><br />
        <label>Latitude <input readonly="readonly" name="Latitude" value="<?php echo $bay['gps_lat'];?>"/></label><br />
		<label>Longitude <input readonly="readonly" name="Longitude"  value="<?php echo $bay['gps_long'];?>"/></label><br />
		<label>map URL<input readonly="readonly" name="mapURL"  value="<?php echo $bay['mapURL'];?>"/></label><br />
		<label>Width<input readonly="readonly"  name="Width" value="<?php echo $bay['width'];?>"/></label><br/>
		<label>Height<input readonly="readonly" name="Height"  value="<?php echo $bay['height'];?>"/></label><br />
		<label>Length<input readonly="readonly" name="Length"  value="<?php echo $bay['length'];?>"/></label><br />
		<label>Pod<input readonly="readonly"  name="Pod" value="<?php echo $bay['pod'];?>"/></label><br />
		<label>Week Start<input readonly="readonly"   name="WeekStart"  value="<?php echo $bay['avail_wk_start'];?>"/></label><br />
		<label>Week End<input readonly="readonly"  name="WeekEnd" value="<?php echo $bay['avail_wk_end'];?>"/></label><br />
		<label>Weekend Start<input readonly="readonly" name="Weekend Star"  value="<?php echo $bay['avail_wend_start'];?>"/></label><br />
		<label>Weekend End<input readonly="readonly" name="WeekendEnd"  value="<?php echo $bay['avail_wend_end'];?>"/></label><br />
		<label>Owner<input readonly="readonly" name="Owner"  value="<?php echo $bay['owner'];?>"/></label><br /><br /><br />

    </form>
<?php
echo '<a href="newbooking.php?bayID=',$bayID,'"> Make New Booking';
htmlFoot();
?>
