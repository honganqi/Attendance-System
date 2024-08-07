<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../../src/classes/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

if (isset($_GET['date'])) {
	$return = [];
	$logs = new Attendance($_GET['date']);
	if (isset($_GET['student'])) {
		$data = $logs->getStudentRawEntries($_GET['student']);
		if ($data) {
			$return = $data;
		}
		echo json_encode($return);
	}
	
}

if (isset($entry)) echo json_encode($entry->transaction);