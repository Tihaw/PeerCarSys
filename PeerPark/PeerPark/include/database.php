<?php
/**
 * Database functions. You need to modify each of these to interact with the database and return appropriate results.
 */

/**
 * Connect to database
 * This function does not need to be edited - just update config.ini with your own
 * database connection details.
 * @param string $file Location of configuration data
 * @return PDO database object
 * @throws exception
 */
	
function connect($file = 'config.ini') {
	// read database seetings from config file
    if ( !$settings = parse_ini_file($file, TRUE) )
        throw new exception('Unable to open ' . $file);

    // parse contents of config.ini
    $dns = $settings['database']['driver'] . ':' .
            'host=' . $settings['database']['host'] .
            ((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '') .
            ';dbname=' . $settings['database']['schema'];
    $user= $settings['db_user']['username'];
    $pw  = $settings['db_user']['password'];

	// create new database connection
    try {
        $dbh=new PDO($dns, $user, $pw);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        print "Error Connecting to Database: " . $e->getMessage() . "<br/>";
        die();
    }
    return $dbh;
}

/**
 * Check login details
 * @param string $name Login name
 * @param string $pass Password
 * @return boolean True is login details are correct
 */
function checkLogin($name,$pass) {
    // STUDENT TODO:
    // Replace line below with code to validate details from the database
    //	
	
	//for login via nickname
	$dbh = connect();
	$sql = "select nickname,password, memberNo from member where nickname = ?";
	$stmt = $dbh->prepare($sql);
	
	$true_name = $name;
	$true_pass = 'random';
	
	$stmt->setFetchMode(PDO::FETCH_OBJ);
	if ($stmt->execute(array($name)))
	{
		while ($row = $stmt->fetch())
		{
			$true_name = $row->memberNo;
			$true_pass = $row->password;
			$name = $row->memberNo;
		}
		if($pass == $true_pass)
			return true;
	}
	
	//for login via email
	$sql = "select email,password,memberNo from member where email = ?";
	$stmt = $dbh->prepare($sql);
	
	$true_name = $name;
	$true_pass = 'random';
	
	$stmt->setFetchMode(PDO::FETCH_OBJ);
	if ($stmt->execute(array($name)))
	{
		while ($row = $stmt->fetch())
		{
			$true_name = $row->memberNo;
			$true_pass = $row->password;
			$name = $row->memberNo;
		}
		if($pass == $true_pass)
			return true;
	}
	//both wrong
	return false;
}

/**
 * Get details of the current user
 * @param string $user login name user
 * @return array Details of user - see index.php
 */
function getUserDetails($user) {
    // STUDENT TODO:
    // Replace lines below with code to validate details from the database
	
	$dbh = connect();
	$sql = "SELECT member.memberno,member.nickname,member.email,member.adrstreet,member.prefbay,member.prefbillingno,member.stat_nrofbookings,CreditCard.brand,ParkBay.site FROM member INNER JOIN CreditCard ON member.memberno = CreditCard.memberNo INNER JOIN ParkBay ON member.prefbay = ParkBay.bayID WHERE nickname = ? OR email = ?";
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_OBJ);
	
	if ($stmt->execute(array($user,$user)))
	{
		while ($row = $stmt->fetch())
		{
				//print_r($row);
			    $results = array();
				$results['memberNo'] = $row->memberno;
				$results['name'] = $row->nickname;
				$results['address'] = $row->adrstreet;
				$results['email'] = $row->email;
				$results['prefBillingNo'] = $row->prefbillingno;
				$results['prefBillingName'] = $row->brand;
				$results['prefBay'] = $row->prefbay;
				$results['prefBayName'] = $row->site;
				$results['nbookings'] = $row->stat_nrofbookings;
		}
	}
	else
	{
		throw new Exception('DB error');
	}
	return $results;
}

/**
 * Get list of bays with silimar address
 * @param string $address address to be look up
 * @return array Various details of each bay - see baylist.php
 */
function searchBay($address) {
    // STUDENT TODO:
    // Change lines below with code to retrieve the Bays with similar address from the database
	$dbh = connect();
	$sql = "SELECT ParkBay.bayid,ParkBay.site,ParkBay.address,Booking.bookingDate FROM ParkBay LEFT JOIN Booking ON ParkBay.bayid = Booking.bayid  WHERE ParkBay.address LIKE ?";
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_OBJ);
	//echo $address;
	$results = array();	
	if ($stmt->execute(array("%$address%")))
	{	
		$num = 0;
		while ($row = $stmt->fetch())
		{
			$result = array();
			$result['bayID'] = $row->bayid;
			$result['site'] = $row->site;
			$result['address'] = $row->address;
			if($row->bookingdate)//if found booked
			{
				$result['avail'] = false;
			}
			else
			{
				$result['avail'] = true;
			}
			$results[$num] = $result;
			$num = $num + 1;
		}
	}
	else
	{
		throw new Exception('DB error');
	}
	//print_r($results);
    return $results;
}

/**
 * Retrieve information of all bays
  * @return array Various details of each bay - see baylist.php
 * @throws Exception
 */

function getBays() {
    // STUDENT TODO:
    // Replace lines below with code to get list of bays from the database
    // Example booking info - this should come from a query. Format is
	// (bay ID, site, address, availability of the bay)
	$dbh = connect();
	$sql = "SELECT ParkBay.bayid,ParkBay.site,ParkBay.address,Booking.bookingDate FROM ParkBay LEFT JOIN Booking ON ParkBay.bayid = Booking.bayid";
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_OBJ);
	//echo $address;
	$results = array();	
	if ($stmt->execute())
	{	
		$num = 0;
		while ($row = $stmt->fetch())
		{
			$result = array();
			$result['bayID'] = $row->bayid;
			$result['site'] = $row->site;
			$result['address'] = $row->address;
			if($row->bookingdate)//if found booked
			{
				$result['avail'] = false;
			}
			else
			{
				$result['avail'] = true;
			}
			$results[$num] = $result;
			$num = $num + 1;
		}
	}
	else
	{
		throw new Exception('DB error');
	}
	//print_r($results);
    return $results;
}

/**
 * Retrieve information on bays
 * @param string $memberNo ID of the member
 * @return array  details of the member preferred bay - see baylist.php
 * @throws Exception
 */

function getPrefBayInformation($memberNo) {
    // STUDENT TODO:
    // Replace lines below with code to get the information about the owner preferred bay from the database
    // Example bay info - this should come from a query. Format is
	// (bay ID, Owner, Latitude, Longitude, Address,  width, height, length, pod, site, week start, week end, weekend start, weekend end)
	$dbh = connect();
	$sql = "SELECT Member.prefBay, ParkBay.bayid,ParkBay.site,ParkBay.address,Booking.bookingDate FROM Member LEFT JOIN ParkBay ON Member.prefBay = ParkBay.bayid LEFT JOIN Booking ON ParkBay.bayid = Booking.bayid WHERE Member.nickName = ? OR Member.email = ?";
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_OBJ);
	//echo $memberNo;
	$results = array();	
	if ($stmt->execute(array($memberNo,$memberNo)))
	{	
		$num = 0;
		while ($row = $stmt->fetch())
		{
			$result = array();
			$result['bayID'] = $row->bayid;
			$result['site'] = $row->site;
			$result['address'] = $row->address;
			if($row->bookingdate)//if found booked
			{
				$result['avail'] = false;
			}
			else
			{
				$result['avail'] = true;
			}
			$results[$num] = $result;
			$num = $num + 1;
		}
	}
	else
	{
		throw new Exception('DB error');
	}
	//print_r($results);
    return $results;

}

/**
 * Retrieve information on bays
 * @param string $BayID ID of the bay
 * @return array Various details of the bay - see baydetail.php
 * @throws Exception
 */

function getBayInformation($BayID) {
    // STUDENT TODO:
    // Replace lines below with code to get the information about a specific bay from the database
    // Example bay info - this should come from a query. Format is
	// (bay ID, Owner, Latitude, Longitude, Address,  width, height, length, pod, site, week start, week end, weekend start, weekend end)
		$dbh = connect();
	$sql = "SELECT * FROM ParkBay WHERE bayID = ?";
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_OBJ);
	//echo $memberNo;
	$results = array();	
	if ($stmt->execute(array($BayID)))
	{	
		while ($row = $stmt->fetch())
		{
		//print_r($row);
			
			$results = array('bayID'=>$row->bayid,'site'=>$row->site, 'owner'=>$row->owner, 'address'=> $row->address,'description'=>$row->description,'gps_lat'=>$row->gps_lat,'gps_long'=>$row->gps_long,'locatedAt'=>$row->located_at,'mapURL'=>$row->mapurl,
        'width'=> $row->width,'height'=>$row->height,'length'=>$row->length,'pod'=>$row->pod,'avail_wk_start'=>$row->avail_wk_start,'avail_wk_end'=>$row->avail_wk_end,'avail_wend_start'=>$row->avail_wend_start,'avail_wend_end'=>$row->avail_wend_end);
		}
	}
	else
	{
		throw new Exception('DB error');
	}
	//print_r($results);
    return $results;
}

/**
 * Retrieve information on active bookings for a member
 * @param string $memberNo ID of member
 * @return array Various details of each booking - see bookings.php
 * @throws Exception
 */

function getOpenBookings($memberNo) {
    // STUDENT TODO:
    // Replace lines below with code to get list of bookings from the database
    // Example booking info - this should come from a query. Format is
    // (booking ID,  bay ID, Car Name, Booking start date, booking start time, booking duration)
	
	$dbh = connect();
	$sql = "SELECT Booking.bookingID,  Booking.bayID, Booking.car, Booking.bookingDate, ParkBay.address FROM Booking  LEFT JOIN ParkBay ON ParkBay.bayID = Booking.bayID WHERE memberNo = (SELECT memberNo FROM Member WHERE nickname = :memberNo OR email = :memberNo )";
	$stmt = $dbh->prepare($sql);
	$stmt->bindParam(':memberNo', $memberNo);
	$stmt->setFetchMode(PDO::FETCH_OBJ);
	//echo $address;
	$results = array();	
	if ($stmt->execute())
	{	
		$num = 0;
		while ($row = $stmt->fetch())
		{
			$result = array('bookingID'=>$row->bookingid,'bayLocation'=>$row->address,'car'=>$row->car,'bookingDate'=>$row->bookingdate );
			$results[$num] = $result;
			$num = $num + 1;
		}
	}
	else
	{
		throw new Exception('DB error');
	}
    return $results;
}

/**
 * Make a new booking for a bay
 * @param string $memberNo Member booking the bay
 * @param string $car         Name of the car
 * @param string $bayID       ID of the bay to book
 * @param string $bookingDate the date of the booking
 * @param string $bookingHour the time of the booking
 * @param string $duration    the duration of the booking

 * @return array Various details of current visit - see newbooking.php
 * @throws Exception
 */
function makeBooking($memberNo,$car,$bayID,$bookingDate,$bookingHour,$duration) {
    // STUDENT TODO:
    // Replace lines below with code to create a booking and return the outcome

    $dbh = connect();
	$bookingHour_sql = str_replace(":","",$bookingHour);
	
	$sql_chech_avail = "SELECT bayID FROM Booking WHERE bayID = ?";
	$stmt = $dbh->prepare($sql_chech_avail);
	$stmt->bindParam(1, $bayID);
	if ($stmt->execute())
	{
		while ($row = $stmt->fetch())
		{
			if($row[0])
			{
				echo "<script>alert('Not available, pls select another bay.');</script>";
				exit;
			}
		}
	}
	//echo "inserting start";
	$sql = "INSERT INTO Booking(memberNo,car,bayID,bookingDate,bookingHour,duration) VALUES (  (SELECT memberNo FROM Member WHERE nickname = :memberNo OR email = :memberNo )  ,:car,:bayID,:bookingDate,:bookingHour,:duration) RETURNING bookingid";
	$stmt = $dbh->prepare($sql);
	$stmt->bindParam(':memberNo', $memberNo);
	$stmt->bindParam(':car', $car);
	$stmt->bindParam(':bayID', $bayID);
	$stmt->bindParam(':bookingDate', $bookingDate);
	$stmt->bindParam(':bookingHour', $bookingHour_sql);
	$stmt->bindParam(':duration', $duration);
	
	$stmt->setFetchMode(PDO::FETCH_OBJ);
	
	try
	{
		if ($stmt->execute())
		{
			//echo "insert good";
			while ($row = $stmt->fetch())
			{
				//print_r($row);
				$bookID = $row->bookingid;
			}
			//echo $bookID;
			$sql2 = "SELECT hourly_rate FROM MembershipPlan,member WHERE MembershipPlan.title = Member.plan AND (Member.nickname = :memberNo OR Member.email = :memberNo )";
			$stmt2 = $dbh->prepare($sql2);
			$stmt2->bindParam(':memberNo', $memberNo);
			if ($stmt2->execute())
			{
				//echo "query good";
				while ($row2 = $stmt2->fetch())
				{	
					$cost = $row2[0] * $duration;
				}
				return array(
				'status'=>'success',
				'bookingID'=>$bookID,
				'bayID'=>$bayID,
				'car'=>$car,
				'bookingDate'=>$bookingDate,
				'bookingHour'=>$bookingHour,
				'duration'=>$duration,
				'cost'=>$cost
				 );
			}
		}
		else
		{
			echo "insert bad;";
			throw new Exception('DB error');
		}
	}
	catch (PDOException $e) {
    echo "<script>alert('PLS enter the right car name or the right bayid.');</script>";
}
}

/**
 * Retrieve information on the booking
 * @param string $bookingID ID of the bay
 * @return array Various details of the booking - see bookingDetail.php
 * @throws Exception
 */
function getBookingInfo($bookingID) {
    // STUDENT TODO:
    // Replace lines below with code to get the detail about the booking.
    // Example booking info - this should come from a query. Format is
	// (bookingID, bay Location, booking Date, booking Hour, duration, car, member Name)
	$dbh = connect();
	$sql = "SELECT Booking.bookingID,  Booking.bayID, Booking.car, Booking.bookingDate,Booking.bookingHour,Booking.duration, ParkBay.address FROM Booking  LEFT JOIN ParkBay ON ParkBay.bayID = Booking.bayID WHERE bookingID = :booingID";
	$stmt = $dbh->prepare($sql);
	$stmt->bindParam(':booingID', $bookingID);
	$stmt->setFetchMode(PDO::FETCH_OBJ);

	if ($stmt->execute())
	{	
		while ($row = $stmt->fetch())
		{
			$bookingtime = str_split($row->bookinghour);
			$length = strlen($row->bookinghour);
			$bookingtime_show = "";
			$i = 0;
			while(true)
			{
				$bookingtime_show = $bookingtime[$length-1].$bookingtime_show;
				$length = $length - 1;
				$i = $i + 1;
				if($length  == 0) break;
				if($i % 2 == 0) $bookingtime_show = ":".$bookingtime_show;
			}
			$result = array('bookingID'=>$row->bookingid,'bayLocation'=>$row->address,'bookingDate'=>$row->bookingdate,'bookingHour'=>$bookingtime_show,'duration'=>$row->duration,'car'=> $row->car,'memberName'=> 'WTF');
			return $result;
		}
	}
	else
	{
		throw new Exception('DB error');
	}
	//
}

/**
 * Get details of the cars of the member
 * @param string $user ID of member
 * @return Name of the cars owned by the member - see index.php
 */
function getCars($memberNo) {
    // STUDENT TODO:
    // Change lines below with code to retrieve the cars of the member from the database
	$dbh = connect();
	$sql = "SELECT make , model ,name FROM Member JOIN Car ON Car.memberNo = Member.memberNo WHERE Member.nickName = ? OR Member.email = ?";
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_OBJ);
	//echo $address;
	$results = array();	
	if ($stmt->execute(array($memberNo,$memberNo)))
	{	
		$num = 0;
		while ($row = $stmt->fetch())
		{
			$result = array();
			$result['car'] = $row->make."-".$row->model." name ".$row->name;
			$results[$num] = $result;
			$num = $num + 1;
		}
	}
	else
	{
		throw new Exception('DB error');
	}
    return $results;
}

/**
 * clear the bookings table for test
 */
function clearBookings() {
    // STUDENT TODO:
    // Change lines below with code to retrieve the cars of the member from the database
	$dbh = connect();
	$sql = "DELETE FROM Booking";
	$stmt = $dbh->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_OBJ);
	//echo $address;
	if ($stmt->execute())
	{		
		echo "<script>alert('Booking table clear! new bookings can be tested');</script>";
	}
	else
	{	
		echo 'DB error';
		throw new Exception('DB error');
	}
}

?>
