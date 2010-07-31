<?php

class Folder {
	protected $database;
	
	protected $id;
	protected $name;
	
	public function __construct($id) {
		$this->id = $id;
		
		$this->database = Database::getDatabase();
		$this->database->prepareQuery('SELECT name FROM folders WHERE id = ' . name('id'));
		$this->database->select('Folder-GetFolder')->bindInteger('id', $id);
		
		foreach($this->database->executeQuery() as $row) {
			$this->name = $row->name;
		}
	}
	
	/*
	 
	 private function __construct() {
		$database = Database::getDatabase();
		
		$database->prepareQuery('SELECT name FROM folders WHERE id = ' . $database->name('id'))->storeQuery('Folder-GetFolder');
		$database->prepareQuery('UPDATE folders SET name = ' . $database->name('name') . ' WHERE id = ' . $database->name('id'))->storeQuery('Folder-SetFolderName');
		$database->prepareQuery('SELECT id FROM links WHERE folder = ' . $database->name('id'))->storeQuery('Folder-GetFiles');
		$database->prepareQuery('SELECT id FROM folders WHERE parentfolder = ' . $database->name('id'))->storeQuery('Folder-GetFolders');
		$database->prepareQuery('INSERT INTO folders VALUES(NULL, :id, :name)')->storeQuery('Folder-MakeFolder');
		$database->prepareQuery('SELECT id FROM folders WHERE parentfolder = ' . $database->name('id') . ' AND name = ' . $database->name('name'))->storeQuery('Folder-GetNewFolder');
	}
	
	*/
	
	public function name($name = '') {
		if($name != null && $name != '') {
			$this->database->retrieveQuery('Folder-SetFolderName')->bindString('name', $name)->bindInteger('id', $this->id)->executeQuery();
			$this->name = $name;
		}
		
		return $this->name;
	}
	
	public function getFilesInFolder() {
		$this->database->retrieveQuery('Folder-GetFiles')->bindInteger('id', $this->id)->executeQuery();
		
		$files = array();
		$filesPointer = 0;
		
		foreach($this->database as $row) {
			$files[$filesPointer++] = new File($row->id, true, true, false);
		}
		
		return $files;
	}
	
	public function getFoldersInFolder() {
		$this->database->retrieveQuery('Folder-GetFolders')->bindInteger('id', $this->id)->executeQuery();
		
		echo $this->database . '<br />';
		echo $this->id . '<br />';
		echo $this->database->getNumberOfRows() . '<br />';
		
		$folders = array();
		$foldersPointer = 0;
		
		foreach($this->database as $row) {
			print_r($row);
			$folders[$foldersPointer++] = new Folder(1);
		}
		
		return $folders;
	}
	
	public function makeFolder($name) {
		if(!is_string($name) || $name == '') {
			return;
		}
		
		$name = (string) $name;
		
		$currentFolders = $this->getFoldersInFolder();
		
		foreach($currentFolders as $folder) {			
			if((string) $folder == $name) {
				return;
			}
		}
		
		$this->database->retrieveQuery('Folder-MakeFolder')->bindInteger('id', $this->id)->bindString('name', $name)->executeQuery();
		
		$newFolder = null;
		
		$this->database->retrieveQuery('Folder-GetNewFolder')->bindInteger('id', $this->id)->bindString('name', $name)->executeQuery();
		
		foreach($this->database as $row) {
			$newFolder = new Folder($row->id);
		}
		
		return $newFolder;
	}
	
	public function __toString() {
		return $this->name();
	}
}

?>