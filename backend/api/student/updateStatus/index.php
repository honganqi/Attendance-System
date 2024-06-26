<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/classes/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});


if (isset($_GET['id'])) {
    $returnjson = array();
    $student = new Student($_GET['id']);
    echo json_encode($student);
    $contents = json_decode(trim(file_get_contents("php://input")), true);
    $newStatus = $contents['newStatus'];
    if (isset($newStatus)) {
        $response = $student->updateStatus($newStatus);
    }

    header($response['status_code_header']);
    if ($response['body']) {
        $response['message'] = "Student status updated successfully!";
        $response['messageType'] = "success";
        echo json_encode($response);
    }
} else {
    echo json_encode(array('none' => 'none'));
}