<?php
define('REMOTE_IMG_PATH', __DIR__ . '/../photos/');
define('DOWNLOAD_URL', 'https://themeridian.edu.ph/mis/photos/');

// check data from sender
$data = file_get_contents('php://input');

// URL-decode the data
$processed_data = urldecode($data);

// JSON-decode the data
$processed_data = json_decode($processed_data, JSON_OBJECT_AS_ARRAY);

if (isset($processed_data['missingFiles'])) {
	$missing_list = array();
	$local_files = $processed_data['missingFiles'];

	// check students
	if (isset($local_files['students'])) {
		$local_photos = $local_files['students'];
		$img_dir = REMOTE_IMG_PATH . 'students/';
		$files = array_diff(scandir($img_dir), array('.', '..'));
		foreach ($files as $file) {
			$this_file_time = filemtime($img_dir . $file);
            // echo $file . ': ' . $local_photos[$file] . ' vs ' . $this_file_time . "\n";
            // $local_photos[$file] is probably rounded off thus needing this + 1 comparison
			if (!isset($local_photos[$file]) || ($this_file_time > ($local_photos[$file] + 1))) {
				$missing_list['students'][$file] = $file;
			}
		}
	}


	//var_dump($local_file_list);

	/*
	// check fetchers
	if ($stuff->missingFiles->fetchers) {
		foreach ($stuff->missingFiles->fetchers as $filename => $timestamp) {
			echo $filename . ' - ' . $timestamp . '<br>';
		}	
	}
	*/

}


if (isset($missing_list)) {
	foreach (array('students', 'fetchers') as $target) {
		if (isset($missing_list[$target])) {
			$zip_name = REMOTE_IMG_PATH . $target . '.zip';
            $download_file = DOWNLOAD_URL . $target . '.zip';
			$zip = new ZipArchive();
			$zip->open($zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE);
			foreach ($missing_list[$target] as $filename) {
				$remote_file = REMOTE_IMG_PATH . $target . '/' . $filename;
				$this_file = $target . '/' . $filename;
				if (file_exists($remote_file) AND is_file($remote_file)) {
					$zip->addFile($remote_file, $this_file);
				}
			}
			$zip->close();

			$return['files'][$target] = $download_file;
		}
	}
}

if (isset($return)) {
	header("Content-type: application/json");
	echo json_encode($return);
}

if (isset($downloadFile)) {
	header("Content-type: application/zip");
	header("Content-Disposition: attachment; filename=" . $return['files']['students']);
	header("Content-length: " . filesize($return['files']['students']));
	header("Pragma: no-cache"); 
	header("Expires: 0");

	readfile($return['files']['students']);	
}