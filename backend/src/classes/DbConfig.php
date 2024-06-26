<?php
class DbConfig {
	private string $_host = 'localhost';
	private string $_username = 'root';
	private string $_password = '';
	private string $_db_name = '';
	private string $_port = '3306';
	private string $_con;
	private string $_q_col;
	public $pdo;
	public string $message;
	
	function __construct($remote = null) {
		if (isset($remote)) {
			$this->_host = getenv('REMOTE_MYSQL_HOST');
			$this->_username = getenv('REMOTE_MYSQL_USER');
			$this->_password = getenv('REMOTE_MYSQL_PASSWORD');
			$this->_db_name = getenv('REMOTE_MYSQL_DATABASE');
		} else {
			$this->_host = getenv('MYSQL_HOST');
			$this->_username = getenv('MYSQL_USER');
			$this->_password = getenv('MYSQL_PASSWORD');
			$this->_db_name = getenv('MYSQL_DATABASE');
		}
		$this->connect();
	}

	function connect() {
		try {
			$charset = 'utf8';
			$dsn = "mysql:host=" . $this->_host . ";port=" . $this->_port . ";dbname=" . $this->_db_name . ";charset=$charset";
		
			$options = [
				PDO::ATTR_ERRMODE				=> PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE	=> PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES		=> false,
			];
			
			$pdo = new PDO($dsn, $this->_username, $this->_password, $options);
			$this->pdo = $pdo;
		}
		catch (PDOException $e) {
			echo $e->getMessage() . "<br><br>";
			$this->message = 'Unable to connect';
		}
	}
}
