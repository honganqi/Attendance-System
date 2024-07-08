<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/classes/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

$list = [];
$student = new Student();

$inactive = "";
if (isset($_GET) && isset($_GET['inactive']) && ($_GET['inactive'] == "include" || $_GET['inactive'] == "only")) {
    $inactive = $_GET['inactive'];
}
if ($tempList = $student->getList($inactive)) {
    $list = $tempList;
}
echo json_encode($list);