<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/Models/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

$returnjson = array();

if (isset($_GET['date'])) {
	$logs = new Attendance($_GET['date']);
	
    $data = $logs->getList();
	if ($data) {
		$returnjson = $data;
	}
}

echo json_encode($returnjson);