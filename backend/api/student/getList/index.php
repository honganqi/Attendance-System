<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/classes/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

$list = [];
$student = new Student();

$inactive = false;
if (isset($_GET) && isset($_GET['inactive'])) {
    $inactive = filter_var($_GET['inactive'], FILTER_VALIDATE_BOOLEAN);
}
if ($tempList = $student->getList($inactive)) {
    $list = $tempList;
}
echo json_encode($list);