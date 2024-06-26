<?php
spl_autoload_register(function($class) {
    $path = __DIR__ . '/../../../src/classes/' . str_replace('\\', '/', $class . '.php');
	if (file_exists($path)) require $path;
});

$list = [];
$student = new Student();
if ($tempList = $student->getList()) {
    $list = $tempList;
}
echo json_encode($list);