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
}

?>