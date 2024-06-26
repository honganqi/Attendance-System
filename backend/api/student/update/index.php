<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/classes/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});


if (isset($_GET['id'])) {
    $returnjson = array();
    $student = new Student($_GET['id']);
    $contents = json_decode(trim(file_get_contents("php://input")), true);
    $newData = $contents['newData'];
    if (isset($newData)) {
        $response = $student->updateRecord($newData);
    } else {
        $delete = $contents['delete'];
        if (isset($delete)) {
            $response = $student->deleteRecord();
        }
    }

    header($response['status_code_header']);
    if ($response['body']) {
        $response['message'] = "Student record updated successfully!";
        $response['messageType'] = "success";
        echo json_encode($response);
    }
} else {
    echo json_encode(array('none' => 'none'));
}