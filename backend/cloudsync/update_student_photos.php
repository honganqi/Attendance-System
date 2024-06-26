<?php
define('LOCAL_IMG_PATH', '/var/www/html/photos/');
define('REMOTE_IMG_PATH', '/public_html/mis/photos/');

function listDetailed($resource, $target_type) {
	global $local_file_list;
	$newcount = 0;
	$search_dir = REMOTE_IMG_PATH . $target_type . '/';
	if (is_array($children = @ftp_rawlist($resource, $search_dir))) {
		$items = array();
		$zip = new ZipArchive();
		$zip->open(REMOTE_IMG_PATH . $target_type . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

		foreach ($children as $child) {
			$chunks = preg_split("/\s+/", $child);
			list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time'], $item['name']) = $chunks;
			$item['type'] = $chunks[0][0] === 'd' ? 'directory' : 'file';
			array_splice($chunks, 0, 8);
			$month = date_parse($item['month']);
			// run time/year logic: time is displayed instead of year when the modtime is not more than 6 months ago
			if (strpos($item['time'], ':')) {
				$item['stamp'] = mktime(substr($item['time'], 0, 2), substr($item['time'], -2), 0, $month['month'], $item['day']);
			} else {
				$item['stamp'] = mktime(0, 0, 0, $month['month'], $item['day'], $item['time']);
			}
			if (strpos($item['time'], ':')) {
				$timenow = date("U");
				if ($item['stamp'] > $timenow) $item['stamp'] = mktime(0, 0, 0, $month['month'], $item['day'], date("Y") - 1);
				//echo date("Y-m-d H:i", $item['stamp']) . ' - ' . $item['month'] . ' ' . $item['day'] . ' ' . $item['time'] . "\n";
			}
			unset($item['rights']);
			unset($item['number']);
			unset($item['user']);
			unset($item['group']);
			unset($item['month']);
			unset($item['day']);
			unset($item['time']);
			unset($item['name']);
			if ($item['type'] == "file") {
				$filename = implode(" ", $chunks);

				// if file does not exist locally
				// or remote file is newer
				if (
					!isset($local_file_list[$target_type][$filename])
					|| (isset($local_file_list[$target_type][$filename]) AND ($item['stamp'] > ($local_file_list[$target_type][$filename])))
					) {
					// add to zip
					//echo  " --- " . $search_dir . $filename . "<br>";
					//$file = file_get_contents('/var/www/html/mis/photos/' . $target_type . '/' . $filename);
					//$zip->addFromString(pathinfo ( $search_dir . $filename, PATHINFO_BASENAME), $file);
					//$zip->addFile($search_dir . $filename);
					$items[$filename] = $item['stamp'];
					$newcount++;
				}
			}
		}

		//$zip->close();
		return $items;
	} 

// Throw exception or return false < up to you 
}


function fetchLinks($data) {
	$url = "https://themeridian.edu.ph/mis/ajax/zip_missing_locals.php";
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);

	$headers = array(
	   "Content-Type: application/json",
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$senddata = urlencode(json_encode(array('missingFiles' => $data)));

	curl_setopt($curl, CURLOPT_POSTFIELDS, $senddata);
	$resp = curl_exec($curl);
	curl_close($curl);
	$response = json_decode($resp);
	if ($response->files) {
		if ($response->files->students) {
			downloadFile($response->files->students, "students");
		}
	} else {
		echo "No files to download";
	}
}

function downloadFile($url, $target) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
	$raw_file_data = curl_exec($curl);
	if (curl_errno($curl)) {
		$error_msg = curl_error($curl);
	}
	curl_close($curl);

	$downloadPath = LOCAL_IMG_PATH . $target . '.zip';

	file_put_contents($downloadPath, file_get_contents($url));

	$zip = new ZipArchive;
	if ($zip->open($downloadPath) === TRUE) {
		$zip->extractTo(LOCAL_IMG_PATH);
		$zip->close();
	}
	return (filesize($downloadPath) > 0)? true : false;	
}


// make a list of all local files
foreach (array('students', 'fetchers') as $target) {
	$local_file_path = LOCAL_IMG_PATH . $target . '/';
	$local_files = array_diff(scandir($local_file_path), array('.', '..'));
	/* exempt all directories from the scandir function above */
	$local_files = array_filter($local_files, function($file) use ($local_file_path) {
		return !is_dir($local_file_path . $file);
	});
	foreach ($local_files as $file) {
		$local_file_list[$target][$file] = filemtime($local_file_path . $file);
	}
}

fetchLinks($local_file_list);



/*
// connect to FTP
$ftp_server = "ftp.themeridian.edu.ph";
$ftp_username = "themerid";
$ftp_password = "-3xwvB~({bXh";

echo "connecting<br>\n";
$conn_id = ftp_connect($ftp_server, 21, 5) or die("Couldn't connect");
echo "logging in<br>\n";
$login_result = ftp_login($conn_id, $ftp_username, $ftp_password);
echo "activating passive mode<br>\n";
ftp_pasv($conn_id, TRUE);

foreach (array('students', 'fetchers') as $target) {
	$remote_file_list = listDetailed($conn_id, $target);

	echo "fetching $target files<br>\n";
	$remote_file_list = listDetailed($conn_id, $target);
	echo "listing files: " . count($remote_file_list) . " files<br>\n";
	echo '<pre>';
	print_r($remote_file_list);
	echo '</pre>';

	$local_file_path = LOCAL_IMG_PATH . $target . '/';
	$remote_file_path = REMOTE_IMG_PATH . $target . '/';
	$local_files = array_diff(scandir($local_file_path), array('.', '..'));
	$local_files = array_filter($local_files, function($file) use ($local_file_path) {
		return !is_dir($local_file_path . $file);
	});
	foreach ($local_files as $file) {
		$local_file_list[$file]['stamp'] = filemtime($local_file_path . $file);
	}

	$newcount = 0;
	$updatecount = 0;
	foreach ($remote_file_list as $filename => $filedetails) {
		echo $filedetails . '<br>';
		if (!isset($local_file_list[$filename])) {
			//echo "fetching: $filename\n";
			ftp_get($conn_id, $local_file_path . $filename, $remote_file_path . $filename, FTP_BINARY);
			touch($local_file_path . $filename, $filedetails);
			$newcount++;
		} elseif (isset($local_file_list[$filename]) AND ($filedetails > ($local_file_list[$filename]['stamp']))) {
			//echo "updating: $filename\n";
			ftp_get($conn_id, $local_file_path . $filename, $remote_file_path . $filename, FTP_BINARY);
			touch($local_file_path . $filename, $filedetails);
			$updatecount++;
		}
	}

	if ($newcount > 0) echo "New $target photos: $newcount\n";
	if ($updatecount > 0) echo "Updated $target photos: $updatecount\n";
}
*/