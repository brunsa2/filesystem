<?php

class Folder {
	protected $database;
	
	protected $id;
	protected $name;
	
	public function __construct($id) {
		$this->id = $id;
		
		$this->database = Database::getDatabase();
		$this->database->prepareQuery('SELECT name FROM folders WHERE id = ' . $this->database->name('id'))->storeQuery('Folder-GetFolder');
		$this->database->bindInteger('id', $id)->executeQuery();
		
		foreach($this->database as $row) {
			$this->name = $row->name;
		}
		
		$this->database->prepareQuery('UPDATE folders SET name = ' . $this->database->name('name') . ' WHERE id = ' . $this->database->name('id'))->storeQuery('Folder-SetFolderName');
		$this->database->prepareQuery('SELECT id FROM links WHERE folder = ' . $this->database->name('id'))->storeQuery('Folder-GetFiles');
		$this->database->prepareQuery('SELECT id FROM folders WHERE parentfolder = ' . $this->database->name('id'))->storeQuery('Folder-GetFolders');
		$this->database->prepareQuery('INSERT INTO folders VALUES(NULL, :id, :name)')->storeQuery('Folder-MakeFolder');
		$this->database->prepareQuery('SELECT id FROM folders WHERE parentfolder = ' . $this->database->name('id') . ' AND name = ' . $this->database->name('name'))->storeQuery('Folder-GetNewFolder');
	}
	
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
		
		$folders = array();
		$foldersPointer = 0;
		
		foreach($this->database as $row) {
			$folders[$foldersPointer++] = new Folder($row->id);
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