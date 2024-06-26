<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

$hour = date('G');
$day  = date('N'); // 1..7 for Monday to Sunday

date_default_timezone_set("Asia/Manila");

$studentlocaldata = array();
$fetcherlocaldata = array();
$personnellocaldata = array();

$dbconfig = new DbConfig();
$pdo = $dbconfig->pdo;
$remoteconfig = new DbConfig("remote");
$remotepdo = $remoteconfig->pdo;

// check remote connection
if (isset($remoteconfig->message)) {
	//echo "<p>Connection to web server failed!</p>";
	//echo "<p><strong>$remotedb->connect_error</strong></p>";
	//echo "<p>Last attempt $timenow.</p>";
	$timenow = date("Y-m-d H:i:s");
	$error_stmt = $pdo->prepare("UPDATE attendance_sync SET lastsync = ? WHERE tablename = ?");
	$error_stmt->execute(array($timenow, "remoteerror"));
} else {

	/*
	// fetch last student data from REMOTE
	$studentonlinequery = $remotepdo->prepare("SELECT MAX(ID) as maxid FROM attendance_raw");
	$studentonlinequery->execute();
	$studentonlinerow = $studentonlinequery->fetch();
	$studentremotemax = $studentonlinerow['maxid'];

	// fetch last student data from LOCAL
	$studentnewquery = $pdo->prepare("SELECT ID, student, time, in_out, fetcher FROM attendance_raw WHERE ID > ?");
	$studentnewquery->execute(array($studentremotemax));
	if ($studentnewquery->rowCount() > 0) {
		while ($studentnewrow = $studentnewquery->fetch()) {
			$studentlocaldata[$studentnewrow['ID']] = array(
				"student" => $studentnewrow['student'],
				"time" => $studentnewrow['time'],
				"in_out" => $studentnewrow['in_out'],
				"fetcher" => $studentnewrow['fetcher']
			);
		}
	}

	// process LOCAL to REMOTE update
	if (!empty($studentlocaldata)) {
		$studentatt_sql = "INSERT INTO attendance_raw (ID, student, time, in_out, fetcher) VALUES (?, ?, ?, ?, ?)";
		$studentatt = $remotepdo->prepare($studentatt_sql);
		foreach ($studentlocaldata as $id => $studentlocaldetails) {
			$studentatt->execute(array($id, $studentlocaldetails['student'], $studentlocaldetails['time'], $studentlocaldetails['in_out'], $studentlocaldetails['fetcher']));
		}
	}





	// fetch last fetcher data from REMOTE
	$fetcheronlinequery = $remotepdo->prepare("SELECT MAX(ID) as maxid FROM family_fetcher_log");
	$fetcheronlinequery->execute();
	$fetcheronlinerow = $fetcheronlinequery->fetch();
	$fetcherremotemax = $fetcheronlinerow['maxid'];

	// fetch last fetcher data from LOCAL
	$fetchernewquery = $pdo->prepare("SELECT ID, time, fetcher FROM family_fetcher_log WHERE ID > ?");
	$fetchernewquery->execute(array($fetcherremotemax));
	if ($fetchernewquery->rowCount() > 0) {
		while ($fetchernewrow = $fetchernewquery->fetch()) {
			$fetcherlocaldata[$fetchernewrow['ID']] = array(
				"time" => $fetchernewrow['time'],
				"fetcher" => $fetchernewrow['fetcher']
			);
		}
	}

	// process LOCAL to REMOTE update
	if (!empty($fetcherlocaldata)) {
		$fetcheratt_sql = "INSERT INTO family_fetcher_log (ID, time, fetcher) VALUES (?, ?, ?)";
		$fetcheratt = $remotepdo->prepare($fetcheratt_sql);
		foreach ($fetcherlocaldata as $id => $fetcherlocaldetails) {
			$fetcheratt->execute(array($id, $fetcherlocaldetails['time'], $fetcherlocaldetails['fetcher']));
		}
	}
	*/




	// fetch last personnel data from REMOTE
	$personnelonlinequery = $remotepdo->prepare("SELECT MAX(ID) as maxid FROM attendance");
	$personnelonlinequery->execute();
	$personnelonlinerow = $personnelonlinequery->fetch();
	$personnelremotemax = $personnelonlinerow['maxid'];

	// fetch last personnel data from LOCAL
	$personnelnewquery = $pdo->prepare("SELECT ID, user, time, in_out FROM attendance WHERE ID > ?");
	$personnelnewquery->execute(array($personnelremotemax));
	if ($personnelnewquery->rowCount() > 0) {
		while ($personnelnewrow = $personnelnewquery->fetch()) {
			$personnellocaldata[$personnelnewrow['ID']] = array(
				"user" => $personnelnewrow['user'],
				"time" => $personnelnewrow['time'],
				"in_out" => $personnelnewrow['in_out']
			);
		}
	}

	// process LOCAL to REMOTE update
	if (!empty($personnellocaldata)) {
		$personnelatt_sql = "INSERT INTO attendance (ID, user, time, in_out) VALUES (?, ?, ?, ?)";
		$personnelatt = $remotepdo->prepare($personnelatt_sql);
		foreach ($personnellocaldata as $id => $personnellocaldetails) {
			$personnelatt->execute(array($id, $personnellocaldetails['user'], $personnellocaldetails['time'], $personnellocaldetails['in_out']));
		}
	}




	/*
	// fetch last personnel_nonregular data from REMOTE
	$personnel_nonregular_onlinequery = $remotepdo->query("SELECT MAX(ID) as maxid FROM personnel_nonregular_attendance");
	$personnel_nonregular_onlinequery->execute();
	$personnel_nonregular_onlinerow = $personnel_nonregular_onlinequery->fetch();
	$personnel_nonregular_remotemax = $personnel_nonregular_onlinerow['maxid'];

	// fetch last personnel_nonregular data from LOCAL
	$personnel_nonregular_newquery = $pdo->prepare("SELECT ID, user, time, in_out FROM personnel_nonregular_attendance WHERE ID > ?");
	$personnel_nonregular_newquery->execute(array($personnel_nonregular_remotemax));
	if ($personnel_nonregular_newquery->rowCount() > 0) {
		while ($personnel_nonregular_newrow = $personnel_nonregular_newquery->fetch()) {
			$personnel_nonregular_localdata[$personnel_nonregular_newrow['ID']] = array(
				"user" => $personnel_nonregular_newrow['user'],
				"time" => $personnel_nonregular_newrow['time'],
				"in_out" => $personnel_nonregular_newrow['in_out']
			);
		}
	}

	// process LOCAL to REMOTE update
	if (!empty($personnel_nonregular_localdata)) {
		$personnel_nonregular_att_sql = "INSERT INTO personnel_nonregular__attendance (ID, user, time, in_out) VALUES (?, ?, ?, ?)";
		$personnel_nonregular_att = $remotepdo->prepare($personnel_nonregular_att_sql);
		foreach ($personnel_nonregular_localdata as $id => $personnel_nonregular_localdetails) {
			$personnel_nonregular_att->execute(array($id, $personnel_nonregular_localdetails['user'], $personnel_nonregular_localdetails['time'], $personnel_nonregular_localdetails['in_out']));
		}
	}
	*/






	/* LIBRARY
	// fetch last library transaction data from REMOTE
	//$libraryonlinequery = $remotedb->query("SELECT MAX(ID) as maxid FROM library_borrowinglog");
	//$libraryonlinerow = $libraryonlinequery->fetch_assoc();
	$libraryonlinequery = $remotepdo->query("SELECT MAX(ID) as maxid FROM library_borrowinglog");
	$libraryonlinerow = $libraryonlinequery->fetch(PDO::FETCH_ASSOC);
	$libraryremotemax = $libraryonlinerow['maxid'];

	// fetch last library transaction data from LOCAL
	$library_borrowinglog_fields = array("borrower_type", "borrower_id", "book", "borrow_date", "due_date", "return_date", "settle_date", "payment", "status");
	$library_borrowlogs = $pdo->query("SELECT ID, " . implode(", ", $library_borrowinglog_fields) . " FROM library_borrowinglog")->fetchAll();
	if ($library_borrowlogs) {
		$library_insert_sql = "INSERT INTO library_borrowinglog (ID, borrower_type, borrower_id, book, borrow_date, due_date, return_date, settle_date, payment, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$library_insert = $remotepdo->prepare($library_insert_sql);
		$library_update_sql = "UPDATE library_borrowinglog SET ";
		foreach ($library_borrowinglog_fields as $fieldname) $library_update_sql .= $fieldname . " = ?, ";
		$library_update_sql = rtrim($library_update_sql, ", ");
		$library_update_sql .= " WHERE ID = ?";
		$library_update = $remotepdo->prepare($library_update_sql);


		foreach ($library_borrowlogs as $librarylocaldetails) {
			if ($librarylocaldetails['ID'] > $libraryremotemax) {
				$library_args = array($librarylocaldetails['ID']);
				foreach ($library_borrowinglog_fields as $fieldname) $library_args[] = $librarylocaldetails[$fieldname];
				$library_insert->execute($library_args);
			} else {
				$library_args = array();
				foreach ($library_borrowinglog_fields as $fieldname) $library_args[] = $librarylocaldetails[$fieldname];
				$library_args[] = $librarylocaldetails['ID'];
				$library_update->execute($library_args);
			}
		}
	}
	*/










} // if (!$remotedb)