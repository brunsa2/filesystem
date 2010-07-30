<?php

class Database implements Iterator {
	private static $instance;
	
	private $databaseConnection;
	private $preparedQuery;
	
	private $results;
	private $valid;
	private $numberOfRows;
	private $numberOfRowsLeft;
	private $numbersOfRowsAffected;
	private $currentRow;
	
	public static function getDatabase() {
		if(self::$instance == null) {
			self::$instance = new Database();
		}
		
		return self::$instance;
	}
	
	private function __construct() {
		$configuration = Config::getConfigurationData();
		
		$dsn = $configuration['database']['driver'] . ':host=' . $configuration['database']['host'] .
			((!empty($configuration['database']['port'])) ? (';port=' .
			$configuration['database']['port']) : '') . ';dbname=' .$configuration['database']['database'];
		
		try {
			$this->databaseConnection = new PDO($dsn, $configuration['database']['username'],
				$configuration['database']['password']);
		} catch(Exception $thrownException) {
			echo 'Cannot connect to database';
			exit;
		}
	}
	
	public function prepareQuery($query) {
		$this->preparedQueries['current'] = $this->databaseConnection->prepare($query);
		return $this;
	}
	
	public function name($parameterName) {
		return ':' . $parameterName;
		return $this;
	}
	
	public function bindString($name, $data, $length = 255) {
		$name = ':' . $name;
		$this->preparedQueries['current']->bindParam($name, $data, PDO::PARAM_STR, $length);
		return $this;
	}
	
	public function bindInteger($name, $data, $length = 11) {
		$name = ':' . $name;
		$this->preparedQueries['current']->bindParam($name, $data, PDO::PARAM_INT, $length);
		return $this;
	}
	
	public function executeQuery($queryName = '') {
		$preparedQuery = $this->preparedQueries['current'];
		if($queryName != null && $queryName != '') {
			$preparedQuery = $this->preparedQueries['stored'][$queryName];
		}
		
		$preparedQuery->execute();
		
		$this->results = array();
		$this->valid = false;
		$this->numberOfRowsLeft = $this->numberOfRows = $this->currentRow =
			$this->numbersOfRowsAffected = 0;
			
		while($row = $preparedQuery->fetchObject()) {
			$this->results[$this->numberOfRows++] = $row;
		}
		
		$this->numberOfRowsLeft = $this->numberOfRows;
		$this->numbersOfRowsAffected = $preparedQuery->rowCount();
		$this->currentRow = 0;
		$this->valid = $this->numberOfRows != 0;
		
		return $this;
	}
	
	public function current() {
		$this->numberOfRowsLeft--;
		return $this->results[$this->currentRow];
	}
	
	public function rewind() {
		$this->currentRow = 0;
	}
	
	public function key() {
		return $this->currentRow;
	}
	
	public function next() {
		if($this->currentRow < $this->numberOfRows - 1) {
			$this->currentRow++;
		} else {
			$this->valid = false;
		}
	}
	
	public function valid() {
		return $this->valid;
	}
	
	public function getNumberOfRows() {
		return $this->numberOfRows;
	}
	
	public function getNumberOfRowsLeft() {
		return $this->numberOfRowsLeft;
	}
	
	public function getNumberOfRowsAffected() {
		return $this->numbersOfRowsAffected;
	}
	
	public function storeQuery($name) {
		$this->preparedQueries['stored'][$name] = $this->preparedQueries['current'];
		return $this;
	}
	
	public function retrieveQuery($name) {
		$this->preparedQueries['current'] = $this->preparedQueries['stored'][$name];
		return $this;
	}
	
	public function __toString() {
		return $this->preparedQueries['current']->queryString;
	}
}

?>