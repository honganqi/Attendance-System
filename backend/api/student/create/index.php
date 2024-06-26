<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/classes/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});


$returnjson = array();
$contents = json_decode(trim(file_get_contents("php://input")), true);
$newData = $contents['newData'];

if (isset($newData)) {
    $student = new Student();
    $response = $student->createRecord($newData);
}

if ($response['body']) {
    $response['message'] = "New student record created successfully!";
    $response['messageType'] = "success";
    echo json_encode($response);
}