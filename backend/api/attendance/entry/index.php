<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/classes/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

if (isset($_GET['idnumber'])) {
    $returnjson = [];
	$record = new Attendance();

    if (isset($_GET['test'])) {
        $record->testEntry($_GET['idnumber']);
    } else {
        $record->entry($_GET['idnumber']);
    }

    if ($record->studentId) {
        if ($record->studentId != "notfound") {
            $returnjson['transaction'] = $record->transaction;
            $student = new Student($record->studentId);
            if (isset($_GET['test'])) {
                $student->testRecord();
            } else {
                $student->getRecord();
            }
            $returnjson['student'] = $student;
        } else {
            $returnjson['notfound'] = "notfound";
        }
    }
}

if (isset($returnjson)) echo json_encode($returnjson);