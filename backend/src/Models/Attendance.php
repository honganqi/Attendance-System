<?php
class Attendance {
	private $_pdo;
	public $transaction;
	private $_dateNow;
	public $studentId;
	public $error;
	public $errorMessage;
	public function __construct($datestring = '') {
		$dbconfig = new DbConfig();
		$this->_pdo = $dbconfig->pdo;
		
		if (isset($datestring) AND (strlen(trim($datestring)) == 8)) {
			$year = substr($datestring, 0, 4);
			$month = substr($datestring, 4, 2);
			$date = substr($datestring, 6, 2);

			$this->_dateNow = date("Y-m-d", strtotime("$year-$month-$date"));
		} else {
			$this->_dateNow = date("Y-m-d");
		}
	}

	function getList() {
		$data = [];
		$string = "
		SELECT
			MIN(CASE WHEN userAction = 0 THEN timeEntry ELSE NULL END) AS 'in',
			MAX(CASE WHEN userAction = 1 THEN timeEntry ELSE NULL END) AS 'out',
			nickname as name,
			CONCAT(lastname, ', ', firstname, IF (suffix IS NULL or suffix = '', '', CONCAT(' ', suffix)), IF (middlename IS NULL or middlename = '', '', CONCAT(' ', SUBSTR(middlename, 1, 1), '.'))) as fullname,
			students.id
		FROM
			attendance
		JOIN students ON attendance.student = students.id
		WHERE DATE(timeEntry) = ?
		GROUP BY
			DATE(timeEntry)
		ORDER BY
			timeEntry";
		$query = $this->_pdo->prepare($string);
		$query->execute(array($this->_dateNow));

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch()) {
				$data[] = $row;
			}
		}

		return $data;
	}

	function getStudentRawEntries($studentId) {
		$data = [];
		$string = "
		SELECT timeEntry, userAction FROM attendance
		WHERE DATE(timeEntry) = ?
		ORDER BY timeEntry
		";
		$query = $this->_pdo->prepare($string);
		$query->execute(array($this->_dateNow));

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch()) {
				$data[] = $row;
			}
		}

		return $data;
	}

	function entry($idnumber = '') {
		$transaction = [];
        try {
            if (trim($idnumber) != '') {
                $pdo = $this->_pdo;

                // get student UUID and details from NFC ID
				$student = new Student();
                $data = $student->getStudentIDFromIDNumber($idnumber, $forAttendance = true);
				if ($student->id) {
					// userAction defaults to "in" if no entry is found for student yet
					$inoutInsert = 0;
					$timenow = date("Y-m-d H:i:s");
		
					// get student attendance logs
					$inoutquery = $pdo->prepare("SELECT student, timeEntry, userAction, DATE_FORMAT(timeEntry,'%h:%i:%S %p') as timedisplay FROM attendance WHERE DATE(timeEntry) = DATE(NOW()) AND student = ? ORDER BY timeEntry DESC LIMIT 15");
					$inoutquery->execute(array($student->id));
	
					// insert attendance entry
					$insert = $pdo->prepare("INSERT INTO attendance (student, timeEntry, userAction) VALUES (?, ?, ?)");
					if ($inoutquery->rowCount() > 0) {
						while ($inoutrow = $inoutquery->fetch()) {
							if (!isset($last_action))
								$last_action = array('time' => $inoutrow['timeEntry'], 'action' => intval($inoutrow['userAction']));
							$in_out[] = array('time' => date("h:i:s A", strtotime($inoutrow['timeEntry'])), 'action' => intval($inoutrow['userAction']));
						}
						
						// check if student tapped again too soon after their previous entry (e.g. playing with their cards)
						// modify the TAP_TIMEOUT constant to change this
						if (strtotime(date(DATE_ATOM)) > (strtotime($last_action['time']) + Student::TAP_TIMEOUT)) {
							if ($last_action['action'] == 0) $inoutInsert = 1;
							$insert->execute(array($student->id, $timenow, $inoutInsert));
							$transaction['inoutInsert'] = $inoutInsert;
							$transaction['log'] = $in_out;
						} else {
							$inoutInsert = $last_action['action'];
							if (isset($in_out)) unset($in_out[0]);
							if (isset($in_out) AND !empty($in_out)) {
								$in_out = array_values($in_out);					
								$transaction['log'] = $in_out;
							}
							$transaction['inoutInsert'] = $inoutInsert;
							$transaction['double'] = array('time' => date("h:i:s A", strtotime($last_action['time'])), 'action' => $last_action['action']);
						}
					} else {
						$insert->execute(array($student->id, $timenow, $inoutInsert));
						$transaction['inoutInsert'] = $inoutInsert;
						$transaction['first'] = true;
					}
				} else {
                    throw new Exception($data['errorMessage']);
                }

            } else {
                throw new Exception("ID number is empty");
            }
        } catch (Exception $e) {
            $transaction['error'] = $e->getMessage();
        }

		$this->transaction = $transaction;
		if ($student->id) {
			$this->studentId = $student->id;
		}
	}

	// testEntry: selects a student from a preset static array and generates a random set of entry and exit logs for them
	function testEntry($idnumber = '') {
		$transaction = [];
        try {
			// $ids[$idnumber] = studentId string
			$ids = array(
				'11111111' => 'aaaaaaaa',
				'22222222' => 'bbbbbbbb',
				'33333333' => 'cccccccc',
				'44444444' => 'dddddddd',
				'55555555' => 'eeeeeeee',
				'66666666' => 'ffffffff',
				'77777777' => 'gggggggg',
				'88888888' => 'hhhhhhhh',
			);

			if (trim($idnumber == '')) {
				$idnumber = array_rand($ids, 1);
			}

			// get student UUID and details from NFC ID
			$student = new Student();
			$student->testStudentIDFromIDNumber($idnumber);
			if ($student->id) {
				// userAction defaults to "in" if no entry is found for student yet
				$inoutInsert = 0;
				$timenow = date("Y-m-d H:i:s");
	
				// iterate through randomly generated timestamps
				$maxEntries = rand(0, 8);  // Maximum number of fake entries/exits
				
				if ($maxEntries > 0) {
					$timestamps = $this->generateRandomTimestamps($maxEntries);
					foreach ($timestamps as $entry) {
						if (!isset($last_action))
							$last_action = array('time' => $entry['timeEntry'], 'action' => intval($entry['userAction']));
						$in_out[] = array('time' => date("h:i:s A", strtotime($entry['timeEntry'])), 'action' => intval($entry['userAction']));
					}
											
					// check if student tapped again too soon after their previous entry (e.g. playing with their cards)
					// modify the TAP_TIMEOUT constant to change this
					if (strtotime(date(DATE_ATOM)) > (strtotime($last_action['time']) + Student::TAP_TIMEOUT)) {
						if ($last_action['action'] == 0) $inoutInsert = 1;
						$transaction['inoutInsert'] = $inoutInsert;
						$transaction['log'] = $in_out;
					} else {
						$inoutInsert = $last_action['action'];
						if (isset($in_out)) unset($in_out[0]);
						if (isset($in_out) AND !empty($in_out)) {
							$in_out = array_values($in_out);					
							$transaction['log'] = $in_out;
						}
						$transaction['inoutInsert'] = $inoutInsert;
						$transaction['double'] = array('time' => date("h:i:s A", strtotime($last_action['time'])), 'action' => $last_action['action']);
					}
				} else {
					$transaction['inoutInsert'] = $inoutInsert;
					$transaction['first'] = true;
				}
			} else {
				$this->studentId = "notfound";
				throw new Exception("ID is not assigned to any student");
			}
        } catch (Exception $e) {
            $transaction['error'] = $e->getMessage();
        }

		$this->transaction = $transaction;
		if ($student->id) {
			$this->studentId = $student->id;
		}
	}

	function generateRandomTimestamps($maxTimestampsPerArray) {
		$currentTime = time();
		$records = [];

		for ($j = 0; $j < $maxTimestampsPerArray; $j++) {
			do {
				// Generate a random hour (0 to 23)
				$hour = rand(0, 23);

				// Generate a random minute (0 to 59)
				$minute = rand(0, 59);

				// Generate a random second (0 to 59)
				$second = rand(0, 59);

				// Create a timestamp for the generated time today
				$generatedTime = mktime($hour, $minute, $second);

			} while ($generatedTime >= $currentTime);

			// Format the timestamp (HH:MM:SS)
			$timestamp = sprintf('%02d:%02d:%02d', $hour, $minute, $second);
			$timestamps[] = $timestamp;
		}

		sort($timestamps);

		$inoutAction = 0;
		foreach ($timestamps as $timestamp) {
			$entry = array(
				'timeEntry' => $timestamp,
				'userAction' => $inoutAction
			);
			$inoutAction == 0 ? 1 : 0;
			array_push($records, $entry);
		}

		return $records;
	}
}