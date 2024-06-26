<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

$hour = date('G');
$day  = date('N'); // 1..7 for Monday to Sunday

date_default_timezone_set("Asia/Manila");

// ************* REMOTE TO LOCAL SYNC
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
}

//$tablequery = "SELECT ID, tabletosync, HOUR(time) as hour, DAY(time) as day, DATE(time) as date FROM attendance_sync";
$tablequery = "SELECT tablename, HOUR(lastsync) as hour, DAY(lastsync) as day, DATE(lastsync) as date, lastsync, regular FROM attendance_sync WHERE tablename <> ?";
$tableresult = $pdo->prepare($tablequery);
$tableresult->execute(array('remoteerror'));
if ($tableresult->rowCount() > 0) {
	while ($lastsyncrow = $tableresult->fetch()) {
		$tablestosync[$lastsyncrow['tablename']] = $lastsyncrow['tablename'];
	}
}

if (isset($tablestosync)) foreach ($tablestosync as $table) updateLocalWithRemote($table);


function updateLocalWithRemote($table) {
	global $pdo;
	global $remotepdo;
	$success = 0;
	
	switch($table) {
		/*
		cases disabled since undefined fields cause an error in MySQL strict mode
		case('students'):
			$selected = "ID, lastname, firstname, middlename, suffix, lrn, std_no, family, chinese, nickname, email, birthdate, gender, status";
			$fieldnames = array_map('trim', explode(',', $selected));
			break;
		case('personnel_nonregular'):
			$selected = "ID, lastname, firstname, middlename, suffix, prefix, status, idnumber, photo";
			$fieldnames = array_map('trim', explode(',', $selected));
			break;
		case('personnel'):
			$selected = "ID, lastname, firstname, middlename, suffix, prefix, status, idnumber, photo, usertype, position";
			$fieldnames = array_map('trim', explode(',', $selected));
			break;
		case('family_fetcher'):
			$selected = "ID, fetcher_no, family, relationship, lastname, firstname, middlename, suffix, image, idnumber, active";
			$fieldnames = array_map('trim', explode(',', $selected));
			break;
		case('student_photo'):
			$selected = "ID, student, schoolyear, path, idnumber, date, active, notes";
			$fieldnames = array_map('trim', explode(',', $selected));
			break;
			*/
		default:
			$selected = "*";
			$fieldname_query = $pdo->prepare("DESCRIBE " . $table);
			$fieldname_query->execute();
			$fieldnames = $fieldname_query->fetchAll(PDO::FETCH_COLUMN);
			break;
	}

	$fetchremote_query = "SELECT $selected FROM $table";
	$fetchremote_result = $remotepdo->query($fetchremote_query);


	$insertfield = "";
	$pdoinsert = "";
	$pdoupdate = "";
	foreach ($fieldnames as $fieldname) {
		$insertfield .= "$fieldname, ";
		$pdoinsert .= ":" . $fieldname . ", ";
		$pdoupdate .= "$fieldname = :" . $fieldname . "_update, ";
	}
	$insertfield = rtrim($insertfield, ", ");
	$pdoinsert = rtrim($pdoinsert, ", ");
	$pdoupdate = rtrim($pdoupdate, ", ");
	$insert = "INSERT INTO $table ($insertfield) VALUES ($pdoinsert) ON DUPLICATE KEY UPDATE $pdoupdate";
	$stmt = $pdo->prepare($insert);

	if ($fetchremote_result->rowCount() > 0) {
		$query_args = array();
		while ($row = $fetchremote_result->fetch()) {
			
			foreach ($fieldnames as $fieldname) {
				if (!isset($row[$fieldname])) $row[$fieldname] = null;
				$query_args[':' . $fieldname] = $row[$fieldname];
				$query_args[':' . $fieldname . '_update'] = $row[$fieldname];
			}
			if ($stmt->execute($query_args)) $success = 1;
		}
	}

	if ($success == 1) {
		$timenow = date("Y-m-d H:i:s");
		$update_synctable = $pdo->prepare("UPDATE attendance_sync SET lastsync = ? WHERE tablename = ?");
		$update_synctable->execute(array($timenow, $table));
	}
}