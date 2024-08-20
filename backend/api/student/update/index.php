<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/Models/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

$response = array(
    'status_code_header' => "HTTP/1.1 400 Bad Request",
    'message' => "There was an error updating the student record",
    'messageType' => "error"
);

$data = json_decode(trim(file_get_contents("php://input")));
if (
    property_exists($data, 'id')
    AND property_exists($data, 'newData')
    AND isset($_GET['id'])
    AND $data->id == $_GET['id']
    ) {
    $student = new Student($data->id);
    $response = $student->update($data->newData);
}

header($response['status_code_header']);
echo json_encode($response);