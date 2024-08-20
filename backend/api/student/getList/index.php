<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/Models/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

$list = [];
$student = new Student();

$filters = new stdClass();

if (isset($_GET) && isset($_GET['inactive'])) {
    $filters->status = filter_var($_GET['inactive'], FILTER_VALIDATE_BOOLEAN);
}
if ($tempList = $student->index($filters)) {
    $list = $tempList;
}
echo json_encode($list);