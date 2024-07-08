<?php
#[\AllowDynamicProperties]
class Student {
	private $_pdo;
    public $id;
    public $lastname;
    public $firstname;
    public $middlename;
    public $suffix;
    public $nickname;
    public $birthdate;
    public $gender;
	public $status;
    public $fullname;
    public $splitName = [
        'lastname' => '',
        'firstname' => '',
        'middlename' => '',
        'suffix' => ''
    ];
    // set timeout from last ID tap
    public const TAP_TIMEOUT = 3;

	public function __construct($studentId = 0) {
        $dbconfig = new DbConfig();
        $this->_pdo = $dbconfig->pdo;
        if ($studentId != 0) {
            $this->id = $studentId;
        }
    }

    public function getList($inactive = "") {
        $pdo = $this->_pdo;
        $string = "SELECT id FROM students";
        $args = [];
        if ($inactive == "" || $inactive == "only") {
            $string .= " WHERE status = ?";
            $args = $inactive == "" ? array(1) : array(0);
        }
        $query = $pdo->prepare($string);
        $query->execute($args);

        $students = [];

        if ($query->rowCount() > 0) {
            while ($row = $query->fetch()) {
                $student = new Student($row['id']);
                $student->getRecord();

                $students[] = array(
                    'id' => $student->id,
                    'fullname' => $student->fullname,
                    'nickname' => $student->nickname
                );
            }
        }

        return $students;
    }

    public function getRecord($forAttendance = false) {
        try {
            $string = "";
            $args = array(':id' => $this->id);
            if ($forAttendance) {
                $string = "AND status = :status";
                $args[':status'] = 1;
            }
            // fetch record details based on ID
            $query = $this->_pdo->prepare("SELECT * FROM students WHERE id = :id $string");
            $query->execute($args);

            // check if record exists
            if ($query->rowCount() > 0) {
                $student = $query->fetch();

                // assign fields as object properties
                foreach ($student as $fieldname => $value) {
                    $this->$fieldname = $value;
                }

                // create name properties: fullname and splitName
                $nameArray = $this->makeName([
                    'lastname' => $student['lastname'],
                    'firstname' => $student['firstname'],
                    'middlename' => $student['middlename'],
                    'suffix' => $student['suffix']
                ]);
                $this->splitName = $nameArray;
            }
        } catch (Exception $e) {

        }
    }

    function testRecord() {
        $records = array(
            'a1a1a1a1' => array(
                'lastname' => 'Cardenas',
                'firstname' => 'Simon',
                'middlename' => 'Turner',
                'nickname' => 'Simon',
                'suffix' => '',
                'birthdate' => '2017-09-02',
                'gender' => 'male',
                'status' => 1,
            ),
            'b2b2b2b2' => array(
                'lastname' => 'Holden',
                'firstname' => 'Trey',
                'middlename' => 'Knight',
                'nickname' => 'Trey',
                'suffix' => '',
                'birthdate' => '2019-03-19',
                'gender' => 'male',
                'status' => 1,
            ),
            'c3c3c3c3' => array(
                'lastname' => 'Snyder',
                'firstname' => 'Aurora',
                'middlename' => 'Patel',
                'nickname' => 'Aurora',
                'suffix' => '',
                'birthdate' => '2016-05-20',
                'gender' => 'female',
                'status' => 1,
            ),
            'd4d4d4d4' => array(
                'lastname' => 'Sandoval',
                'firstname' => 'Brooke',
                'middlename' => 'Bolton',
                'nickname' => 'Brooke',
                'suffix' => '',
                'birthdate' => '2013-10-21',
                'gender' => 'female',
                'status' => 1,
            ),
            'e5e5e5e5' => array(
                'lastname' => 'De Leon',
                'firstname' => 'Myah',
                'middlename' => 'Guerrero',
                'nickname' => 'My-My',
                'suffix' => '',
                'birthdate' => '2018-11-15',
                'gender' => 'female',
                'status' => 1,
            ),
            'f6f6f6f6' => array(
                'lastname' => 'Garrett',
                'firstname' => 'Sebastian',
                'middlename' => 'Ryan',
                'nickname' => 'Seb',
                'suffix' => '',
                'birthdate' => '2013-08-12',
                'gender' => 'male',
                'status' => 1,
            ),
            'g7g7g7g7' => array(
                'lastname' => 'Owais',
                'firstname' => 'Paul',
                'middlename' => 'Chan',
                'nickname' => 'Paul',
                'suffix' => '',
                'birthdate' => '2014-01-25',
                'gender' => 'male',
                'status' => 1,
            ),
            'h8h8h8h8' => array(
                'lastname' => 'King',
                'firstname' => 'Eleni',
                'middlename' => 'Daniel',
                'nickname' => 'Lenny',
                'suffix' => '',
                'birthdate' => '2018-07-09',
                'gender' => 'female',
                'status' => 1,
            ),
        );

        $student = $records[$this->id];

        foreach ($student as $fieldname => $value) {
            $this->$fieldname = $value;
        }
        // create name properties: fullname and splitName
        $nameArray = $this->makeName([
            'lastname' => $student['lastname'],
            'firstname' => $student['firstname'],
            'middlename' => $student['middlename'],
            'suffix' => $student['suffix']
        ]);
        $this->splitName = $nameArray;
    }

    function makeName($nameArray) {
        $fullnameSplit['middlename'] = '';
        if ($nameArray['middlename'] && trim($nameArray['middlename']) != '') {
            $fullnameSplit['middlename'] = $nameArray['middlename'][0] . '.';
        }
    
        $fullnameSplit['suffix'] = '';
        if ($nameArray['suffix'] && trim($nameArray['suffix']) != '') {
            $fullnameSplit['suffix'] = ' ' . $nameArray['suffix'];
        }
        
        if ($nameArray['firstname'] && $nameArray['lastname']) {
            $fullnameSplit['firstname'] = $nameArray['firstname'];
            $fullnameSplit['lastname'] = $nameArray['lastname'];
            $this->fullname = $fullnameSplit['lastname'] . ', ' . $fullnameSplit['firstname'] . $fullnameSplit['suffix'] . ' ' . $fullnameSplit['middlename'];
        }
        return $fullnameSplit;
    }

    public function getStudentIDFromIDNumber($idnumber = '', $forAttendance = false) {
        $returnval = [];
        try {
            if (trim($idnumber) != '') {
                $query = $this->_pdo->prepare("SELECT id, status FROM students WHERE idnumber = :idnumber");
                $query->execute(array(':idnumber' => $idnumber));

                if ($query->rowCount() > 0) {
                    $student = $query->fetch();

                    if ($student['status'] == 1 || !$forAttendance) {
                        $this->id = $student['id'];
                        $returnval['id'] = $this->id;
                    } else {
                        $returnval['error'] = "inactive";
                        throw new Exception("ID is assigned to inactive record");
                    }
                } else {
                    $returnval['error'] = "unassigned";
                    throw new Exception("ID is not assigned to any student");
                }
            } else {
                $returnval['error'] = "empty";
                throw new Exception("ID number is empty");
            }
        } catch (Exception $e) {
            $returnval['errorMessage'] = $e->getMessage();
        }
        return $returnval;
    }

    public function testStudentIDFromIDNumber($idnumber = '') {
        $returnval = [];

        // $ids[$idnumber] = studentId string
        $ids = array(
            '11111111' => 'a1a1a1a1',
            '22222222' => 'b2b2b2b2',
            '33333333' => 'c3c3c3c3',
            '44444444' => 'd4d4d4d4',
            '55555555' => 'e5e5e5e5',
            '66666666' => 'f6f6f6f6',
            '77777777' => 'g7g7g7g7',
            '88888888' => 'h8h8h8h8',
        );
        try {
            if (trim($idnumber) == '') {
                $idnumber = array_rand($ids, 1);
            }
            if (array_key_exists($idnumber, $ids)) {
                $this->id = $ids[$idnumber];
                $returnval['id'] = $this->id;
            } else {
                throw new Exception("ID is not assigned to any student");
            }
        } catch (Exception $e) {
            $returnval['error'] = $e->getMessage();
        }
        return $returnval;
    }

	function AttendanceEntry($idnumber = '') {
        try {
            if (trim($idnumber) != '') {
                $pdo = $this->_pdo;
                // get student UUID
                $this->getStudentIDFromIDNumber($idnumber);

                // userAction defaults to "in" if no entry is found for student yet
                $inoutInsert = 0;
                $timenow = date("Y-m-d H:i:s");
    
                // get student attendance logs
                $inoutquery = $pdo->prepare("SELECT student, timeEntry, userAction, DATE_FORMAT(timeEntry,'%h:%i:%S %p') as timedisplay FROM attendance WHERE DATE(timeEntry) = DATE(NOW()) AND student = ? ORDER BY timeEntry DESC LIMIT 15");
                $inoutquery->execute(array($this->id));

                // insert attendance entry
                $insert = $pdo->prepare("INSERT INTO attendance (student, timeEntry, userAction) VALUES (?, ?, ?)");
                if ($inoutquery->rowCount() > 0) {
                    while ($inoutrow = $inoutquery->fetch()) {
                        if (!isset($last_action))
                            $last_action = array('time' => $inoutrow['timeEntry'], 'action' => intval($inoutrow['userAction']));
                        $in_out[] = array('time' => date("h:i:s A", strtotime($inoutrow['timeEntry'])), 'action' => intval($inoutrow['userAction']));
                    }
                    
                    if (strtotime(date(DATE_ATOM)) > (strtotime($last_action['time']) + Student::TAP_TIMEOUT)) {
                        if ($last_action['action'] == 0) $inoutInsert = 1;
                        $insert->execute(array($this->id, $timenow, $inoutInsert));
                        $returnarray['inoutInsert'] = $inoutInsert;
                        $returnarray['log'] = $in_out;
                    } else {
                        $inoutInsert = $last_action['action'];
                        if (isset($in_out)) unset($in_out[0]);
                        if (isset($in_out) AND !empty($in_out)) {
                            $in_out = array_values($in_out);					
                            $returnarray['log'] = $in_out;
                        }
                        $returnarray['inoutInsert'] = $inoutInsert;
                        $returnarray['double'] = array('time' => date("h:i:s A", strtotime($last_action['time'])), 'action' => $last_action['action']);
                    }
                } else {
                    $insert->execute(array($this->id, $timenow, $inoutInsert));
                    $returnarray['inoutInsert'] = $inoutInsert;
                    $returnarray['first'] = true;
                }
    
                if (isset($returnarray)) return $returnarray;                
            } else {
                throw new Exception("ID number is empty");
            }
        } catch (Exception $e) {
            $returnarray['error'] = $e->getMessage();
            return $returnarray;
        }
	}

	function getPhoto($userid, $usertype) {
		$pdo = $this->_pdo;

		switch ($usertype) {
			case "personnel":
				$query = $pdo->prepare("SELECT photo as photopath FROM students WHERE id = ?");
				$query->execute(array($userid));
				break;
		}
		if ($query->rowCount() > 0) {
			while ($row = $query->fetch()) return $row['photopath'];
		} else {
			return false;
		}
	}

    public function createRecord($data) {
        // FUNCTION NEEDS VALIDATION (duplicates, etc.)
        try {
            $pdo = $this->_pdo;

            $newData = array(
                ':lastname' => $data['lastname'],
                ':firstname' => $data['firstname'],
                ':middlename' => $data['middlename'],
                ':suffix' => $data['suffix'],
                ':nickname' => $data['nickname'],
                ':birthdate' => $data['birthdate'],
                ':gender' => $data['gender'],
                ':status' => 1
            );

            $create = $pdo->prepare("
                INSERT INTO students (lastname, firstname, middlename, suffix, nickname, birthdate, gender, status, id)
                VALUES (:lastname, :firstname, :middlename, :suffix, :nickname, :birthdate, :gender, :status, UUID())
                RETURNING id
            ");
            if ($create->execute($newData)) {
                // get UUID of last inserted record
                $this->id = $create->fetchColumn();
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = "success";
                return $response;
            }
        } catch (Exception $e) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['error'] = "error";
            $response['body'] = $e->getMessage();
            return $response;
        }
    }

    public function updateRecord($data) {
        try {
            $pdo = $this->_pdo;

            $newData = array(
                ':lastname' => $data['lastname'],
                ':firstname' => $data['firstname'],
                ':middlename' => $data['middlename'],
                ':suffix' => $data['suffix'],
                ':nickname' => $data['nickname'],
                ':birthdate' => $data['birthdate'],
                ':gender' => $data['gender'],
                ':idnumber' => $data['idnumber'],
                ':id' => $this->id,
            );

            $update = $pdo->prepare("
                UPDATE students SET
                lastname = :lastname,
                firstname = :firstname,
                middlename = :middlename,
                suffix = :suffix,
                nickname = :nickname,
                birthdate = :birthdate,
                gender = :gender,
                idnumber = :idnumber
                WHERE id = :id
            ");
            if ($update->execute($newData)) {
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = "success";
                return $response;
            }
        } catch (Exception $e) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = "error";
            return $response;
        }
    }

    public function deleteRecord() {
        try {
            $pdo = $this->_pdo;

            $data = array(
                ':id' => $this->id,
            );

            $delete = $pdo->prepare("DELETE FROM students WHERE id = :id");
            if ($delete->execute($data)) {
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = "success";
                return $response;
            }
        } catch (Exception $e) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = "error";
            return $response;
        }
    }

    public function updateStatus($newStatus) {
        try {
            $pdo = $this->_pdo;

            $data = array(
                ':status' => $newStatus,
                ':id' => $this->id,
            );

            $update = $pdo->prepare("
                UPDATE students SET
                status = :status
                WHERE id = :id
            ");
            if ($update->execute($data)) {
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = "success";
                return $response;
            }
        } catch (Exception $e) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = "error";
            return $response;
        }
    }
}