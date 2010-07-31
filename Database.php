<?php

class Database {
	private static $instance;
	
	private $databaseConnection;
	private $preparedQueries;
	
	private $currentQuery;
	
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
	
	public function prepareQuery($query, $name) {
		$this->preparedQueries[$name] = $this->databaseConnection->prepare($query);
		$this->currentQuery = $name;
		return $this;
	}
	
	public function select($name) {
		if(is_string($name) && $name != '') {
			$this->currentQuery = $name;
		}
		
		return $this;
	}
	
	public function bindString($name, $data, $length = 255) {
		$name = ':' . $name;
		$this->preparedQueries[$this->currentQuery]->bindParam($name, $data, PDO::PARAM_STR, $length);
		return $this;
	}
	
	public function bindInteger($name, $data, $length = 11) {
		$name = ':' . $name;
		$this->preparedQueries[$this->currentQuery]->bindParam($name, $data, PDO::PARAM_INT, $length);
		return $this;
	}
	
	public function executeQuery($name = '') {
		$preparedQuery = $this->preparedQueries[$this->currentQuery];
		if(is_string($name) && $name != '') {
			$preparedQuery = $this->preparedQueries[$name];
		}
		
		$preparedQuery->execute();
		
		$results = array();
		$resultsPointer = 0;
			
		while($row = $preparedQuery->fetchObject()) {
			$results[$resultsPointer++] = $row;
		}
		
		return $results;
	}
	
	public function getNumberOfRowsAffected($name = '') {
		if(is_string($name) && $name != '') {
			return $this->preparedQueries[$name]->rowCount();
		} else {
			return $this->preparedQueries[$this->currentQuery]->rowCount();
		}
	}
	
	public function __toString() {
		return $this->preparedQueries[$this->currentQuery]->queryString;
	}
}

function name($name) {
	return ':' . $name;
}

?>