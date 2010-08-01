<?php

class Filesystem {
	const READ_ACCESS = 0x01;
	const WRITE_ACCESS = 0x02;
	const APPEND_ACCESS = 0x04;
	const OVERWRITE_ACCESS = 0x08;
	
	private static $instance;
	
	private static $root;
	
	public function getFilesystem() {
		if(self::$instance == null) {
			self::$instance = new Filesystem();
			self::$instance->initializeRoot();
		}
		
		return self::$instance;
	}
	
	private function initializeRoot() {
		self::$root = new Folder(1);
	}
	
	public static function getRoot() {
		return self::$root;
	}
}

?>