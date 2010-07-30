<?php

class Filesystem {
	const READ_ACCESS = 0x01;
	const WRITE_ACCESS = 0x02;
	const APPEND_ACCESS = 0x04;
	const OVERWRITE_ACCESS = 0x08;
	
	private static $instance;
	
	private static $rootTag;
	private static $rootFolder;
	
	public function getFilesystem() {
		if(self::$instance == null) {
			self::$instance = new Filesystem();
			self::$instance->initializeRoots();
		}
		
		return self::$instance;
	}
	
	private function __construct() {
		$database = Database::getDatabase();
		
		$database->prepareQuery('SELECT name FROM folders WHERE id = ' . $this->database->name('id'))->storeQuery('Folder-GetFolder');
		$database->prepareQuery('UPDATE folders SET name = ' . $this->database->name('name') . ' WHERE id = ' . $this->database->name('id'))->storeQuery('Folder-SetFolderName');
		$database->prepareQuery('SELECT id FROM links WHERE folder = ' . $this->database->name('id'))->storeQuery('Folder-GetFiles');
		$database->prepareQuery('SELECT id FROM folders WHERE parentfolder = ' . $this->database->name('id'))->storeQuery('Folder-GetFolders');
		$database->prepareQuery('INSERT INTO folders VALUES(NULL, :id, :name)')->storeQuery('Folder-MakeFolder');
		$database->prepareQuery('SELECT id FROM folders WHERE parentfolder = ' . $this->database->name('id') . ' AND name = ' . $this->database->name('name'))->storeQuery('Folder-GetNewFolder');
	}
	
	private function initializeRoots() {
		self::$rootTag = new Tag(1);
		self::$rootFolder = new Folder(2);
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