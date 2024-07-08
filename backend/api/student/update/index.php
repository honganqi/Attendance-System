<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/classes/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});


if (isset($_GET['id'])) {
    $returnjson = array();
    $student = new Student($_GET['id']);
    $contents = json_decode(trim(file_get_contents("php://input")), true);
    if (array_key_exists('newData', $contents)) {
        $response = $student->updateRecord($contents['newData']);
    } elseif (array_key_exists('delete', $contents)) {
        $response = $student->deleteRecord();
    }

    $verb = array_key_exists('delete', $contents) ? "deleted" : "updated";

    header($response['status_code_header']);
    if ($response['body']) {
        $response['message'] = "Student record $verb successfully!";
        $response['messageType'] = "success";
        echo json_encode($response);
    }
} else {
    echo json_encode(array('none' => 'none'));
}