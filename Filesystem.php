<?php

class Filesystem {
	const READ_ACCESS = 0x01;
	const WRITE_ACCESS = 0x02;
	const APPEND_ACCESS = 0x04;
	const OVERWRITE_ACCESS = 0x08;
	
	private static $instance;
	
	public function getFilesystem() {
		if(self::$instance == null) {
			self::$instance = new Filesystem();
		}
		
		return self::$instance;
	}
	
	private function __construct() {
		
	}
}

?>