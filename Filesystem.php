<?php

class Filesystem {
	const READ_ACCESS = 0x01;
	const WRITE_ACCESS = 0x02;
	const APPEND_ACCESS = 0x04;
	const OVERWRITE_ACCESS = 0x08;
	
	private static $instance;
	
	private static $rootTag = new Tag(1);
	private static $rootFolder = new Folder(2);
	
	public function getFilesystem() {
		if(self::$instance == null) {
			self::$instance = new Filesystem();
		}
		
		return self::$instance;
	}
	
	public static function getRootTag() {
		return self::$rootTag;
	}
	
	public static function getRootFolder() {
		return self::$rootFolder;
	}
	
	private function __construct() {
		
	}
}

?>