<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/classes/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

if (isset($_GET['id'])) {
    $student = new Student($_GET['id']);
    $student->getRecord();

    $returnjson = json_encode($student);
    if (isset($_GET['nameOnly'])) {
        $name = new stdClass();
        $name->fullname = $student->fullname;
        $name->splitName = $student->splitName;
    
        $returnjson = json_encode($name);
    }
    echo $returnjson;
}

if (isset($_GET['idnumber'])) {
    $data = [];
    $student = new Student();
    $data = $student->getStudentIDFromIDNumber($_GET['idnumber']);
    if (isset($data['id'])) {
        $student->getRecord();
        $data['student'] = $student;
    }
    echo json_encode($data);
}