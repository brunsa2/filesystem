<?php

class Database {
	private static $instance;
	
	private $databaseConnection;
	private $preparedQuery;
	
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
		if($queryName == null || $queryName == '') {
			$this->preparedQueries['current']->execute();
		} else {
			$this->preparedQueries['stored'][$queryName]->execute();
		}
		
		return $this;
	}
	
	public function getRow() {
		return $this->preparedQueries['current']->fetch(PDO::FETCH_ASSOC);
	}
	
	public function storeQuery($name) {
		$this->preparedQueries['stored'][$name] = $this->preparedQueries['current'];
		return $this;
	}
	
	public function retrieveQuery($name) {
		$this->preparedQueries['current'] = $this->preparedQueries['stored'][$name];
		return $this;
	}
}

?>