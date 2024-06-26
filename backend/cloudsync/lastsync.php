<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

date_default_timezone_set("Asia/Manila");

$dbconfig = new DbConfig();
$pdo = $dbconfig->pdo;

$sql = "SELECT * FROM attendance_sync ORDER BY regular DESC, tablename ASC";
$query = $pdo->prepare($sql);
$query->execute();
if ($query->rowCount() > 0) {
	while ($row = $query->fetch()) {
		$tabletypes[$row['tablename']] = array('regular' => $row['regular'], 'lastsync' => $row['lastsync']);
	}
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow" />
<title>Meridian Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
</head>


<body>
	<div class="container mt-5">
<?php
if (isset($tabletypes)) {
	echo '<h3>Regulars</h3>';
	echo '<table class="table table-striped table-nonfluid">';
	echo '<thead><tr><th>Table</th><th>Last Sync</th></tr></thead>';
	echo '<tbody>';
	foreach ($tabletypes as $tablename => $details) {
		if ($details['regular'] == 1) {
			echo '<tr>';
			echo '<td>' . $tablename . '</td>';
			echo '<td>' . $details['lastsync'] . '</td>';
			echo '</tr>';	
		}
	}
	echo '</tbody>';
	echo '</table>';

	echo '<h3>Informational</h3>';
	echo '<table class="table table-striped table-nonfluid">';
	echo '<thead><tr><th>Table</th><th>Last Sync</th></tr></thead>';
	echo '<tbody>';
	foreach ($tabletypes as $tablename => $details) {
		if (($details['regular'] == 0) AND ($tablename != "remoteerror")) {
			echo '<tr>';
			echo '<td>' . $tablename . '</td>';
			echo '<td>' . $details['lastsync'] . '</td>';
			echo '</tr>';	
		}
	}
	echo '</tbody>';
	echo '</table>';

	echo '<p>Last error: ' . $tabletypes['remoteerror']['lastsync'] . '</p>';
}
?>
</div>
</body>
</html>