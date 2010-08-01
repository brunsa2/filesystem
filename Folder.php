<?php

class Folder {
	protected $database;
	
	protected $id;
	protected $name;
	
	public function __construct($id) {
		$this->id = $id;
		
		$this->database = Database::getDatabase();
		$this->database->prepareQuery('SELECT name FROM folders WHERE id = ' . name('id'), 'Folder-GetFolder');
		$this->database->select('Folder-GetFolder')->bindInteger('id', $id);
		
		foreach($this->database->executeQuery() as $row) {
			$this->name = $row->name;
		}
		
		$this->database->prepareQuery('UPDATE folders SET name = ' . name('name') . ' WHERE id = ' . name('id'), 'Folder-SetFolderName');
		$this->database->prepareQuery('SELECT id FROM links WHERE folder = ' . name('id'), 'Folder-GetFiles');
		$this->database->prepareQuery('SELECT id FROM folders WHERE parentfolder = ' . name('id'), 'Folder-GetFolders');
		$this->database->prepareQuery('INSERT INTO folders VALUES(NULL, ' . name('id') . ', ' . name('name') . ')', 'Folder-MakeFolder');
		$this->database->prepareQuery('SELECT id FROM folders WHERE parentfolder = ' . name('id') . ' AND name = ' . name('name'), 'Folder-GetSubFolder');
	}
	
	public function name($name = '') {
		if($name != null && $name != '') {
			$this->database->select('Folder-SetFolderName')->bindString('name', $name)->bindInteger('id', $this->id)->executeQuery();
			$this->name = $name;
		}
		
		return $this->name;
	}
	
	public function getFilesInFolder() {
		$this->database->select('Folder-GetFiles')->bindInteger('id', $this->id);
		
		$files = array();
		$filesPointer = 0;
		
		foreach($this->database->executeQuery() as $row) {
			$files[$filesPointer++] = new File($row->id, true, true, false);
		}
		
		return $files;
	}
	
	public function getFoldersInFolder() {
		$this->database->select('Folder-GetFolders')->bindInteger('id', $this->id);
		
		$folders = array();
		$foldersPointer = 0;
		
		foreach($this->database->executeQuery() as $row) {
			$folders[$foldersPointer++] = new Folder($row->id);
		}
		
		return $folders;
	}
	
	public function getSubfolder($name) {
		$this->database->select('Folder-GetSubFolder')->bindInteger('id', $this->id)->bindString('name', $name)->executeQuery();
		
		$subFolder = null;
		
		foreach($this->database->executeQuery() as $row) {
			$subFolder = new Folder($row->id);
		}
		
		return $subFolder;
	}
	
	public function getFolder($path) {
		$folders = null;
		preg_match_all('/[a-zA-Z0-9 \.]+/', $path, $folders);
		$folders = $folders[0];
		
		$parentFolder = $folder = $this;
		
		foreach($folders as $folderName) {
			$folder = $parentFolder->getSubfolder($folderName);
			$parentFolder = $folder;
		}
		
		return $folder;
		
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
		
		$this->database->select('Folder-MakeFolder')->bindInteger('id', $this->id)->bindString('name', $name)->executeQuery();
		
		return $this->getSubfolder($name);
	}
	
	public function __toString() {
		return $this->name();
	}
}

?>